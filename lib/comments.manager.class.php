<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tcommentmanager extends tevents_storage {

  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'commentmanager';
}

  public function getcount() {
    litepublisher::$db->table = 'comments';
    return litepublisher::$db->getcount();
  }
  
  public function add($idpost, $name, $email, $url, $content, $ip) {
$users = tusers::i();
    $idauthor = $comusers->add($name, $email, $url, $ip);
    return $this->addcomment($idpost, $idauthor, $content, $ip);
  }
  
  public function addcomment($idpost, $idauthor, $content, $ip) {
    $status = $this->createstatus($idpost, $idauthor, $content, $ip);
    if (!$status) return false;
    $comments = tcomments::i();
    $id = $comments->add($idpost, $idauthor,  $content, $status, $ip);
    $this->dochanged($id, $idpost);
    $this->added($id, $idpost);
    $this->sendmail($id, $idpost);
    return $id;
  }
  
  public function edit($id, $idpost, $name, $email, $url, $content) {
    $comusers = dbversion ? tcomusers ::i() : tcomusers ::i($idpost);
    $idauthor = $comusers->add($name, $email, $url, '');
    return $this->editcomment($id, $idpost, $idauthor, $content);
  }
  
  public function editcomment($id, $content) {
    $comments = tcomments::i();
    if (!$comments->edit($id, $idauthor,  $content)) return false;
    
    $this->dochanged($id, $idpost);
    $this->edited($id, $idpost);
    return true;
  }
  
  public function reply($idparent, $content) {
$idauthor = 1; //admin
    $status = 'approved';
    $comments = tcomments::i();
$idpost = $comments->getvalue($idparent, 'post');
    $id = $comments->add($idpost, $idauthor,  $content, $status, '');
$comments->setvalue($id, 'parent', $idreply);
    
    $this->dochanged($id, $idpost);
    $this->added($id, $idpost);
    //$this->sendmail($id, $idpost);
    return $id;
  }
  
  private function dochanged($id, $idpost) {
      $comments = tcomments::i();
      $count = $comments->db->getcount("post = $idpost and status = 'approved'");
      $comments->getdb('posts')->setvalue($idpost, 'commentscount', $count);
      //update trust
      try {
        $item = $comments->getitem($id);
        $idauthor = $item['author'];
        $comusers = tcomusers::i($idpost);
        $comusers->setvalue($idauthor, 'trust', $comments->db->getcount("author = $idauthor and status = 'approved' limit 5"));
      } catch (Exception $e) {
      }
    }
    
    $post = tpost::i($idpost);
    $post->clearcache();
    $this->changed($id, $idpost);
  }
  
  public function delete($id) {
    $comments = tcomments::i();
    if ($comments->delete($id)) {
      $this->deleted($id, $idpost);
      $this->dochanged($id, $idpost);
      return true;
    }
    return false;
  }
  
  public function setstatus($id, $$status) {
    if (!in_array($status, array('approved', 'hold', 'spam')))  return false;
    $comments = tcomments::i($idpost);
    if ($comments->setstatus($id, $status)) {
      $this->dochanged($id, $idpost);
      return true;
    }
    return false;
  }
  
  public function checktrust($value) {
    return $value >= $this->trustlevel;
  }
  
  public function trusted($idauthor) {
    if (!dbversion) return true;
    $comusers = tcomusers::i(0);
    $item = $comusers->getitem($idauthor);
    return $this->checktrust($item['trust']);
  }
  
  public function sendmail($id, $idpost) {
    if (!$this->sendnotification) return;
    $comments = tcomments::i($idpost);
    $comment = $comments->getcomment($id);
    ttheme::$vars['comment'] = $comment;
    $args = targs::i();
    $adminurl = litepublisher::$site->url . '/admin/comments/'. litepublisher::$site->q . "id=$id&post=$idpost";
    $ref = md5(litepublisher::$secret . $adminurl);
    $adminurl .= "&ref=$ref&action";
    $args->adminurl = $adminurl;
    
    $mailtemplate = tmailtemplate::i('comments');
    $subject = $mailtemplate->subject($args);
    $body = $mailtemplate->body($args);
    tmailer::sendtoadmin($subject, $body, true);
  }
  
  //status supports only db version
  public function getrecent($count, $status = 'approved') {
      $db = litepublisher::$db;
      $result = $db->res2assoc($db->query("select $db->comments.*,
      $db->users.name as name, $db->users.email as email, $db->users.website as url,
      $db->posts.title as title, $db->posts.commentscount as commentscount,
      $db->urlmap.url as posturl
      from $db->comments, $db->users, $db->posts, $db->urlmap
      where $db->comments.status = '$status' and
      $db->users.id = $db->comments.author and
      $db->posts.id = $db->comments.post and
      $db->urlmap.id = $db->posts.idurl and
      $db->posts.status = 'published' and
      $db->posts.idperm = 0
      order by $db->comments.posted desc limit $count"));
      
      if (litepublisher::$options->commentpages && !litepublisher::$options->comments_invert_order) {
        foreach ($result as $i => $item) {
          $page = ceil($item['commentscount'] / litepublisher::$options->commentsperpage);
          if ($page > 1) $result[$i]['posturl']= rtrim($item['posturl'], '/') . "/page/$page/";
        }
      }
      return $result;
  }
  
}//class