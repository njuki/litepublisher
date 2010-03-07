<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tcommentmanager extends tevents {
  public $items;
  private static $mail; // send mail on shutdown
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'commentmanager';
    $this->addevents('added', 'deleted', 'edited', 'changed', 'approved',
    'authoradded', 'authordeleted', 'authoredited');
    if (!dbversion) $this->addmap('items', array());
    $this->data['sendnotification'] =  true;
    $this->data['trustlevel'] = 2;
    $this->data['hidelink'] = false;
    $this->data['redir'] = true;
    $this->data['nofollow'] = false;
    $this->data['recentcount'] =  7;
    $this->data['maxrecent'] =  20;
  }
  
  public function getcount() {
    if (!dbversion)  return 0;
    litepublisher::$db->table = 'comments';
    return litepublisher::$db->getcount();
  }
  
  private function indexofrecent($id, $idpost) {
    foreach ($this->items as $i => $item) {
      if ($id == $item['id'] && $idpost == $item['idpost']) return $i;
    }
    return false;
  }
  
  private function deleterecent($id, $idpost) {
    if ($i = $this->indexofrecent($id, $idpost)) {
      array_splice($this->items, $i, 1);
      $this->save();
    }
  }
  
  private function addrecent($id, $idpost) {
    if ($i = $this->indexofrecent($id, $idpost))  return;
    $post = tpost::instance($idpost);
    if ($post->status != 'published') return;
    $item = $post->comments->items[$id];
    $item['id'] = $id;
    
    $item['idpost'] = $idpost;
    $item['title'] = $post->title;
    $item['posturl'] =     $post->lastcommenturl;
    
    $comusers = tcomusers::instance($idpost);
    $author = $comusers->items[$item['author']];
    $item['name'] = $author['name'];
    $item['email'] = $author['email'];
    $item['url'] = $author['url'];
    
    if (count($this->items) >= $this->maxrecent) array_pop($this->items);
    array_unshift($this->items, $item);
    $this->save();
  }
  
  public function add($idpost, $name, $email, $url, $content) {
    $comusers = dbversion ? tcomusers ::instance() : tcomusers ::instance($idpost);
    $idauthor = $comusers->add($name, $email, $url);
    return $this->addcomment($idpost, $idauthor, $content);
  }
  
  public function addcomment($idpost, $idauthor, $content) {
    $status = litepublisher::$classes->spamfilter->createstatus($idauthor, $content);
    $comments = tcomments::instance($idpost);
    $id = $comments->add($idauthor,  $content, $status);
    
    if (!dbversion && $status == 'approved') $this->addrecent($id, $idpost);
    
    $this->dochanged($id, $idpost);
    $this->added($id, $idpost);
    $this->sendmail($id, $idpost);
    
    return $id;
  }
  
  public function edit($id, $idpost, $name, $email, $url, $content) {
    $comusers = dbversion ? tcomusers ::instance() : tcomusers ::instance($idpost);
    $idauthor = $comusers->add($name, $email, $url);
    return $this->editcomment($id, $idpost, $idauthor, $content);
  }
  
  public function editcomment($id, $idpost, $idauthor, $content) {
    $comments = tcomments::instance($idpost);
    if (!$comments->edit($id, $idauthor,  $content)) return false;
    //if (!dbversion && $status == 'approved') $this->addrecent($id, $idpost);
    
    $this->dochanged($id, $idpost);
    $this->edited($id, $idpost);
    return true;
  }
  
  public function reply($idreply, $idpost, $content) {
    $status = 'approved';
    $idpost = (int) $idpost;
    $profile = tprofile::instance();
    $email = $profile->mbox!= '' ? $profile->mbox : litepublisher::$options->fromemail;
    $site = litepublisher::$options->url . litepublisher::$options->home;
    $comusers = tcomusers::instance($idpost);
    $idauthor = $comusers->add($profile->nick, $email, $site);
    $comments = tcomments::instance($idpost);
    $id = $comments->add($idauthor,  $content, $status);
    
    if (!dbversion) $this->addrecent($id, $idpost);
    
    $this->dochanged($id, $idpost);
    $this->added($id, $idpost);
    //$this->sendmail($id, $idpost);
    return $id;
  }
  
  private function dochanged($id, $idpost) {
    if (dbversion) {
      $comments = tcomments::instance($idpost);
      $count = $comments->db->getcount("post = $idpost and status = 'approved'");
      $comments->getdb('posts')->setvalue($idpost, 'commentscount', $count);
      //update trust
      try {
        $item = $comments->getitem($id);
        $idauthor = $item['author'];
        $comusers = tcomusers::instance($idpost);
        $comusers->setvalue($idauthor, 'trust', $comments->db->getcount("author = $idauthor and status = 'approved' limit 5"));
      } catch (Exception $e) {
      }
    }
    
    $post = tpost::instance($idpost);
    $post->clearcache();
    $this->changed($id, $idpost);
  }
  
  public function delete($id, $idpost) {
    $comments = tcomments::instance($idpost);
    if ($comments->delete($id)) {
      if (!dbversion) $this->deleterecent($id, $idpost);
      $this->deleted($id, $idpost);
      $this->dochanged($id, $idpost);
      return true;
    }
    return false;
  }
  
  public function postdeleted($idpost) {
    if (dbversion) {
      $comments = tcomments::instance($idpost);
      $comments->db->update("status = 'deleted'", "post = $idpost");
    } else {
      $deleted = false;
      foreach ($this->items as $i => $item) {
        if ($idpost == $item['idpost']) {
          unset($this->items[$i]);
          //array_splice($this->items, $i, 1);
          $deleted = true;
        }
      }
      if ($deleted) {
        $this->save();
        $this->changed();
      }
    }
  }
  
  public function setstatus($id, $idpost, $status) {
    if (!in_array($status, array('approved', 'hold', 'spam')))  return false;
    $comments = tcomments::instance($idpost);
    if (dbversion) {
      $result = $comments->db->setvalue($id, 'status', $status);
    } else {
      switch ($status) {
        case 'hold':
        $result = $comments->sethold($id);
        if ($result) $this->deleterecent($id, $idpost);
        break;
        
        case 'approved':
        $result = $comments->approve($id);
        if ($result) $this->addrecent($id, $idpost);
        break;
      }
    }
    
    if ($result) $this->dochanged($id, $idpost);
    return $result;
  }
  
  public function checktrust($value) {
    return $value >= $this->trustlevel;
  }
  
  public function trusted($idauthor) {
    if (!dbversion) return true;
    $comusers = tcomusers::instance(0);
    $item = $comusers->getitem($idauthor);
    return $this->checktrust($item['trust']);
  }
  
  public function sendmail($id, $idpost) {
    if (!$this->sendnotification) return;
    $comments = tcomments::instance($idpost);
    $comment = $comments->getcomment($id);
    ttheme::$vars['comment'] = $comment;
    $args = targs::instance();
    $args->adminurl = litepublisher::$options->url . '/admin/comments/'. litepublisher::$options->q . "id=$id&post=$idpost&action";
    $mailtemplate = tmailtemplate::instance('comments');
    $subject = $mailtemplate->subject($args);
    $body = $mailtemplate->body($args);
    //tmailer::sendtoadmin($subject, $body);
    if (!isset(self::$mail)) self::$mail = array();
    self::$mail[] = array('subject' => $subject, 'body' => $body);
    register_shutdown_function(__class__ . '::sendtoadmin');
  }
  
  public static function sendtoadmin() {
    foreach (self::$mail as $item) {
      tmailer::sendtoadmin($item['subject'], $item['body']);
    }
  }
  
  public function getrecent($count) {
    if (dbversion) {
      $db = litepublisher::$db;
      $res = $db->query("select $db->comments.*,
      $db->comusers.name as name,
      $db->posts.title as title, $db->posts.commentscount as commentscount,
      $db->urlmap.url as posturl
      from $db->comments, $db->comusers, $db->posts, $db->urlmap
      where $db->comments.status = 'approved' and
      $db->comusers.id = $db->comments.author and
      $db->posts.id = $db->comments.post and
      $db->urlmap.id = $db->posts.idurl and
      $db->posts.status = 'published'
      order by $db->comments.posted desc limit $count");
      
      $result = $db->res2assoc($res);
      if (litepublisher::$options->commentpages) {
        foreach ($result as $i => $item) {
          $page = ceil($item['commentscount'] / litepublisher::$options->commentsperpage);
          if ($page > 1) $result[$i]['posturl']= rtrim($item['posturl'], '/') . "/page/$page/";
        }
      }
      return $result;
    } else {
      if ($count <= count($this->items)) return $this->items;
      return array_slice($this->items, 0, $count);
    }
  }
  
}//class

?>