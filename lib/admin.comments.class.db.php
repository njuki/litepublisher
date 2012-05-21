<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminmoderator extends tadmincommoncomments {
private $moder;
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }

public function canrequest() {
$this->moder = litepublisher::$options->ingroup('moderator');
 }

public function can($id, $action) {
if ($this->moder) return true;
if (litepublisher::$options->user != tcomments::i()->getvalue($id, 'author')) return false;
$cm = tcommentmanager::i();
switch ($action) {
case 'edit':
return $cm->canedit;

case 'delete':
return $cm->candelete;
}
return false;
}

  public function getcontent() {
    $result = '';
        $comments = tcomments::i();
$cm = tcommentmanager::i();
    $lang = $this->lang;
    $html = $this->html;
    
    switch ($this->name) {
      case 'comments':
      case 'hold':
      
      if ($action = $this->action) {
        $id = $this->idget();
        if (!$comments->itemexists($id)) return $this->notfound;

        switch($action) {
          case 'delete':
if (!$this->can($id, 'delete')) return $html->h4->forbidden;
          if(!$this->confirmed) return $this->confirmdelete($id);
          $comments->delete($id);
          $result .= $html->h4->successmoderated;
          break;
          
          case 'hold':
if (!$this->moder) return $html->h4->forbidden;
          $comments->setstatus($id, 'hold');
          $result .= $this->moderated($id);
          break;
          
          case 'approve':
if (!$this->moder) return $html->h4->forbidden;
          $comments->setstatus($id, 'approved');
          $result .= $this->moderated($id);
          break;
          
          case 'edit':
if (!$this->can($id, 'edit')) return $html->h4->forbidden;
          $result .= $this->editcomment($id);
          break;
          
          case 'reply':
if (!$this->can($id, 'edit')) return $html->h4->forbidden;
          $result .= $this->reply($id);
          break;
        }
      }
      
      $result .= $this->getlist($this->name);
      return $result;
      
      case 'authors':
      $lang->section = 'comments';
      if ($action = $this->action) {
        $id = $this->idget();
        switch ($action) {
          case 'delete':
          if (!$this->confirmed) return $this->getconfirmform($id, $lang->authorconfirmdelete);
          if (!$this->deleteauthor($id)) return $this->notfount;
          $result .= $this->html->h2->authordeleted;
          break;
          
          case 'edit':
          $result .= $this->editauthor($id);
        }
      } else {
        $result .= $this->editauthor(0);
      }
      
      $result .= $this->getauthorslist();
      return $result;
      
      case 'holdrss':
      $rss = trssholdcomments::i();
      $args = targs::i();
      $args->rssurl = $rss->rssurl;
      $args->key = $rss->key;
      $args->count = $rss->count;
      $args->rsstemplate = $rss->template;
      $args->formtitle = $lang->rssurl . sprintf(' <a href="%1$s">%1$s</a>', litepublisher::$site->url . $rss->rssurl);
      
      return $html->adminform('
      [text=key]
      [text=count]
      [editor=rsstemplate]',
      $args);
    }
  }
  
  private function editcomment($id) {
    $comment = new tcomment($id);
    ttheme::$vars['comment'] = $comment;
    $args = targs::i();
    $args->content = $comment->rawcontent;
    $args->adminurl =$this->adminurl . "=$id&action";
    $result = $this->html->info($args);
    $result .= $this->html->editform($args);
    return $result;
  }
  
  private function reply($id) {
    $comment = new tcomment($id);
    ttheme::$vars['comment'] = $comment;
    $args = targs::i();
    $args->adminurl =$this->adminurl . "=$id&action";
    $result = $this->html->info($args);
    $result .= $this->html->replyform();
    return $result;
  }
  
  private function getlist($kind) {
    $result = '';
    $comments = tcomments::i(0);
    $perpage = 20;
    // get total count
    $status = $kind == 'hold' ? 'hold' : 'approved';
$where = "$comments->thistable.status = '$status'";
if ($this->moder) $where .= " and $comments->thistable.author = " . litepublisher::$options->user;
    $total = $comments->db->getcount($where);
    $from = $this->getfrom($perpage, $total);
    $list = $comments->select($where, "order by $comments->thistable.posted desc limit $from, $perpage");
    $html = $this->html;
    $result .= sprintf($html->h4->listhead, $from, $from + count($list), $total);
    $table = $this->createtable();
    $args = targs::i();
    $args->adminurl = $this->adminurl;
    $comment = new tcomment(0);
    ttheme::$vars['comment'] = $comment;
    $body = '';
    foreach ($list as $id) {
      $comment->id = $id;
      $args->id = $id;
      $args->excerpt = tadminhtml::specchars(tcontentfilter::getexcerpt($comment->content, 120));
      $args->onhold = $comment->status == 'hold';
      $args->email = $comment->email == '' ? '' : "<a href='mailto:$comment->email'>$comment->email</a>";
      $args->website =$comment->website == '' ? '' : "<a href='$comment->website'>$comment->website</a>";
      $body .=$html->parsearg($table->body, $args);
    }
    $result .= $table->build($body, $html->tablebuttons());
    
    $theme = ttheme::i();
    $result .= $theme->getpages($this->url, litepublisher::$urlmap->page, ceil($total/$perpage));
    return $result;
  }

  private function moderated($id) {
    $result = $this->html->h4->successmoderated;
    $result .= $this->getinfo($id);
    return $result;
  }
  
  private function getinfo($id) {
    if (!isset(ttheme::$vars['comment'])) ttheme::$vars['comment'] = new tcomment($id);
    $args = targs::i();
    $args->adminurl =$this->adminurl . "=$id&action";
    return $this->html->info($args);
  }
  
  private function confirmdelete($id) {
    $result = $this->getconfirmform($id, $this->lang->confirmdelete);
    $result .= $this->getinfo($id);
    return $result;
  }
  
  private function getconfirmform($id, $confirm) {
    $args = targs::i();
    $args->id = $id;
    $args->action = 'delete';
    $args->adminurl = litepublisher::$site->url . $this->url . litepublisher::$site->q . 'id';
    $args->confirm = $confirm;
    return $this->html->confirmform($args);
  }
  
  private function deleteauthor($uid) {
    $users = tusers::i();
    if (!$users->itemexists($uid)) return false;
    if ('comuser' != $users->getvalue($uid, 'status')) return false;
    $comments = tcomments::i();
    $comments->db->delete("author = $uid");
    $users->delete($uid);
    return true;
  }
  
  private function editauthor($id) {
    $args = targs::i();
    if ($id == 0) {
      $args->id = 0;
      $args->name = '';
      $args->email = '';
      $args->website = '';
      $args->subscribed = '';
    } else {
      $users = tusers::i();
      if (!$users->itemexists($id)) return $this->notfound;
      $args->add($users->getitem($id));
      $args->subscribed = $this->getsubscribed($id);
    }
    return $this->html->authorform($args);
  }
  
  private function getauthorslist() {
    $users = tusers::i();
    $args = targs::i();
    $perpage = 20;
    $total = $users->db->getcount("status = 'comuser'");
    $from = $this->getfrom($perpage, $total);
    $res = $users->db->query("select * from $users->thistable where status = 'comuser' order by id desc limit $from, $perpage");
    $items = litepublisher::$db->res2assoc($res);
    $html = $this->html;
    $result = sprintf($html->h2->authorlisthead, $from, $from + count($items), $total);
    $result .= $html->authorheader();
    $args->adminurl = $this->adminurl;
    foreach ($items as $id => $item) {
      $args->add($item);
      $result .= $html->authoritem($args);
    }
    $result .= $html->authorfooter;
    
    $theme = ttheme::i();
    $result .= $theme->getpages($this->url, litepublisher::$urlmap->page, ceil($total/$perpage));
    return $result;
  }
  
  private function getsubscribed($authorid) {
    $db = litepublisher::$db;
    $authorid = (int) $authorid;
    $users = tusers::i();
    if (!$users->itemexists($authorid))  return '';
    $html = $this->gethtml('moderator');
    $result = '';
    $res = $db->query("select $db->posts.id as id, $db->posts.title as title, $db->urlmap.url as url
    from $db->posts, $db->urlmap
    where $db->posts.id in (select DISTINCT $db->comments.post from $db->comments where author = $authorid)
    and $db->urlmap.id = $db->posts.idurl
    order by $db->posts.posted desc");
    $items = $db->res2assoc($res);
    
    $subscribers = tsubscribers::i();
    $subscribed = $subscribers->getposts($authorid);
    $args = targs::i();
    foreach ($items as $item) {
      $args->add($item);
      $args->subscribed = in_array($item['id'], $subscribed);
      $result .= $html->subscribeitem($args);
    }
    
    return $html->fixquote($result);
  }
  
  public function processform() {
    $result = '';
    parent::processform();
          $comments = tcomments::i();      
    switch ($this->name) {
      case 'comments':
      case 'hold':
      if (isset($_REQUEST['action'])) {
        switch ($_REQUEST['action']) {
          case 'reply':
if (!$this->moder) return $this->html->h4->forbidden;
          $item = $comments->getitem($this->idget() );
          $post = tpost::i( (int) $item['post']);
          $this->manager->reply($this->idget(), $post->id, $_POST['content']);
          return litepublisher::$urlmap->redir($post->lastcommenturl);
          
          case 'edit':
if (!$this->can($id, 'edit')) return $this->html->h4->forbidden;
          $comments->edit($this->idget(), $_POST['content']);
          break;
        }
      } else {
        $status = isset($_POST['approve']) ? 'approved' : (isset($_POST['hold']) ? 'hold' : 'delete');
        foreach ($_POST as $key => $id) {
          if (!is_numeric($id))  continue;
          if (!strbegin($key, 'checkbox-item-')) continue;
          $id = (int) $id;
            if ($status == 'delete') {
if ($this->can($id, 'delete')) $comments->delete($id);
            } else {
              if ($this->moder) $comments->setstatus($id, $status);
            }
          }
        }
      }
      $result = $this->html->h4->successmoderated;
      break;
      
      case 'authors':
      if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit') {
        $id = $this->idget();
        $users = tusers::i();
        if (!$users->itemexists($id)) return $this->notfound;
        if ('comuser' != $users->getvalue($id, 'status')) return $this->notfound;
        $users->edit($id, $_POST);
        $subscribers = tsubscribers::i();
        $subscribed = $subscribers->getposts($id);
        $checked = array();
        foreach ($_POST as $idpost => $value) {
          if (!is_numeric($idpost))  continue;
          $checked [] = $idpost;
        }
        $unsub = array_diff($subscribed, $checked);
        if (count($unsub)) {
          foreach ($unsub as $idpost) {
            $subscribers->delete($idpost, $id);
          }
        }
        
        $result =  $this->html->h2->authoredited;
      }
      break;
      
      case 'holdrss':
      extract($_POST, EXTR_SKIP);
      $rss = trssholdcomments::i();
      $rss->lock();
      $rss->key = $key;
      $rss->count = (int) $count;
      $rss->template = $rsstemplate;
      $rss->unlock();
      $result = '';
      break;
    }
    
    litepublisher::$urlmap->clearcache();
    return $result;
  }
  
  public static function refilter() {
    $db = litepublisher::$db;
    $filter = tcontentfilter::i();
    $from = 0;
    while ($a = $db->res2assoc($db->query("select id, rawcontent from $db->rawcomments where id > $from limit 500"))) {
      $db->table = 'comments';
      foreach ($a as $item) {
        $s = $filter->filtercomment($item['rawcontent']);
        $db->setvalue($item['id'], 'content', $s);
        $from = max($from, $item['id']);
      }
      unset($a);
    }
  }
  
}//class