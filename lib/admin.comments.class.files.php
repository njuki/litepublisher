<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminmoderator extends tadmincommoncomments  {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  protected function getidpost() {
    return (int) tadminhtml::getparam('post', 0);
  }
  
  public function getcontent() {
    $result = '';
    $html = $this->html;
    $lang = $this->lang;
    
    switch ($this->name) {
      case 'comments':
      case 'hold':
      
      if ($action = $this->action) {
        $id = $this->idget();
        $comments = tcomments::i($this->idpost);
        if (!$comments->itemexists($id)) return $this->notfound;
        switch($action) {
          case 'delete':
          if(!$this->confirmed) return $this->confirmdelete($id);
          $this->manager->delete($id, $this->idpost);
          $result .= $html->h2->successmoderated;
          break;
          
          case 'hold':
          $this->manager->setstatus($id, $this->idpost, 'hold');
          $result .= $this->moderated($id, $this->idpost);
          break;
          
          case 'approve':
          $this->manager->setstatus($id, $this->idpost, 'approved');
          $result .= $this->moderated($id, $this->idpost);
          break;
          
          case 'edit':
          $result .= $this->editcomment($id, $this->idpost);
          break;
          
          case 'reply':
          $result .= $this->reply($id, $this->idpost);
          break;
        }
      }
      
      if ($this->idpost == 0) {
        $result .= $this->getpostlist($this->name);
      } else {
        $result .= $this->getlist($this->name, $this->idpost);
      }
      return $result;
      
      case 'pingback':
      if ($action = $this->action) {
        $id = $this->idget();
        $pingbacks = tpingbacks::i($this->idpost);
        if (!$pingbacks->itemexists($id)) return $this->notfound;
        switch($action) {
          case 'delete':
          if(!$this->confirmed) return $this->confirmdelete($id, $this->idpost);
          $pingbacks->delete($id);
          $result .= $html->h2->successmoderated;
          break;
          
          case 'hold':
          $pingbacks->setstatus($id, false);
          $result .= $html->h2->successmoderated;
          break;
          
          case 'approve':
          $pingbacks->setstatus($id, true);
          $result .= $html->h2->successmoderated;
          break;
          
          case 'edit':
          $result .= $this->editpingback($id, $this->idpost);
          break;
        }
      }
      
      if ($this->idpost == 0) {
        $result .= $this->getpostlist($this->name);
      } else {
        $result .= $this->getpingbackslist($this->idpost);
      }
      return $result;
      
      case 'authors':
      $lang->section = 'comments';
      if ($action = $this->action) {
        $id = $this->idget();
        switch ($action) {
          case 'delete':
          if (!$this->confirmed) return $this->getconfirmform($id, $this->idpost, $lang->authorconfirmdelete);
          $comments = tcomments::i($this->idpost);
          $comments->deleteauthor($id);
          $result .= $html->h2->authordeleted;
          break;
          
          case 'edit':
          $result .= $this->editauthor($id, $this->idpost);
        }
      } else {
        $result .= $this->editauthor(0, $this->idpost);
      }
      
      if ($this->idpost == 0) {
        $result .= $this->getpostlist($this->name);
      } else {
        $result .= $this->getauthorslist($this->idpost);
      }
      return $result;
    }
    
  }
  
  private function editcomment($id, $idpost) {
    $comments = tcomments::i($idpost);
    $comment = $comments->getcomment($id);
    ttheme::$vars['comment'] = $comment;
    $args = targs::i();
    $args->content = $comment->content;
    $args->adminurl =$this->adminurl . "=$id&post=$idpost&action";
    $result = $this->html->info($args);
    $result .= $this->html->editform($args);
    return $result;
  }
  
  private function reply($id, $idpost) {
    $comment = tcomments::i($idpost, $id);
    ttheme::$vars['comment'] = $comment;
    $args = targs::i();
    $args->adminurl =$this->adminurl . "=$id&post=$idpost&action";
    $result = $this->html->info($args);
    $result .= $this->html->replyform();
    return $result;
  }
  
  private function getlist($status, $idpost) {
    $result = '';
    $comments = tcomments::i($idpost);
    if ($status == 'hold') $comments = $comments->hold;
    $perpage = 20;
    // get total count for all cases
    $total = $comments->count;
    $from = $this->getfrom($perpage, $total);
    $list = array_slice(array_keys($comments->items), $from, $perpage);
    $html = $this->html;
    $result .= sprintf($html->h2->listhead, $from, $from + count($list), $total);
    $table = $this->createtable();
    $args = targs::i();
    $args->adminurl = litepublisher::$site->url .$this->url . litepublisher::$site->q . "post=$idpost&id";
    $comment = new TComment($comments);
    ttheme::$vars['comment'] = $comment;
    $body = '';
    foreach ($list as $id) {
      $comment->id = $id;
      $args->id = $id;
      $args->excerpt = tcontentfilter::getexcerpt($comment->content, 120);
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
  
  private function getpostlist($status) {
    $result = '';
    $posts = tposts::i();
    $perpage = 20;
    $count = $posts->count;
    $from = $this->getfrom($perpage, $count);
    $items = array_slice($posts->items, $from, $perpage, true);
    $items = array_reverse (array_keys($items));
    
    $html = $this->html;
    $head =sprintf($html->h2->postscount, $from, $from + count($items), $count);
    $args = targs::i();
    $args->adminurl = litepublisher::$site->url .$this->url . litepublisher::$site->q . 'post';
    foreach ($items  as $id ) {
      $post = tpost::i($id);
      ttheme::$vars['post'] = $post;
      $result .= $html->postitem($args);
      $result .= "\n";
    }
    $result = sprintf($html->postlist, $result);
    $result = $head . $result;
    $result = str_replace("'", '"', $result);
    
    $theme = ttheme::i();
    $result .= $theme->getpages($this->url, litepublisher::$urlmap->page, ceil($count/$perpage));
    return $result;
  }
  
  private function getpingbackslist($idpost) {
    $result = '';
    $pingbacks = tpingbacks::i($idpost);
    $perpage = 20;
    $total = $pingbacks->getcount();
    $from = $this->getfrom($perpage, $total);
    $list = array_slice(array_keys($pingbacks->items), $from, $perpage);
    $html = $this->html;
    $result .= sprintf($html->h2->pingbackhead, $from, $from + count($items), $total);
    $args = targs::i();
    $args->adminurl = litepublisher::$site->url .$this->url . litepublisher::$site->q . "post=$idpost&id";
    $post = tpost::i($idpost);
    $args->posttitle =$post->title;
    $args->postlink = $post->link;
    foreach ($items as $id) {
      $item = $pingbacks->items[$id];
      $args->add($item);
      $args->id = $id;
      $args->website = sprintf("<a href='%s'>%s</a>", $item['url']);
      $status = $item['approved'] ? 'approved' : 'hold';
      $args->localstatus = tlocal::get('commentstatus', $status);
      $args->date = tlocal::date(strtotime($item['posted']));
      $result .=$html->pingbackitem($args);
    }
    $result .= $html->tablefooter();
    $result = $html->fixquote($result);
    
    $theme = ttheme::i();
    $result .= $theme->getpages($this->url, litepublisher::$urlmap->page, ceil($total/$perpage));
    return $result;
  }
  
  private function editpingback($id, $idpost) {
    $pingbacks = tpingbacks::i($idpost);
    $args = targs::i();
    $args->add($pingbacks->getitem($id));
    return $this->html->pingbackedit($args);
  }
  
  private function moderated($id, $idpost) {
    $result = $this->html->h2->successmoderated;
    $result .= $this->getinfo($id, $idpost);
    return $result;
  }
  
  private function getinfo($id, $idpost) {
    if (!isset(ttheme::$vars['comment'])) {
      $comments = tcomments::i($idpost);
      $comment = $comments->getcomment($id);
      ttheme::$vars['comment'] = $comment;
    }
    
    $args = targs::i();
    $args->adminurl =$this->adminurl . "=$id&post=$idpost&action";
    return $this->html->info($args);
  }
  
  private function confirmdelete($id, $idpost) {
    $result = $this->getconfirmform($id, $this->lang->confirmdelete);
    $result .= $this->getinfo($id, $idpost);
    return $result;
  }
  
  private function getconfirmform($id, $idpost, $confirm) {
    $args = targs::i();
    $args->id = "$id&post=$idpost";
    $args->action = 'delete';
    $args->adminurl = litepublisher::$site->url . $this->url . litepublisher::$site->q . "idpost=$idpost&id";
    $args->confirm = $confirm;
    return $this->html->confirmform($args);
  }
  
  private function editauthor($id, $idpost) {
    $args = targs::i();
    if ($id == 0) {
      $args->id = "0&post=$idpost";
      $args->name = '';
      $args->email = '';
      $args->url = '';
      $args->ip = '127.0.0.1';
      $args->subscribed = '';
    } else {
      $comusers = tcomusers::i($idpost);
      if (!$comusers->itemexists($id)) return $this->notfound;
      $args->add($comusers->getitem($id));
      $args->id = "id&post=$idpost";
      $args->subscribed = $this->getsubscribed($id, $idpost);
    }
    return $this->html->authorform($args);
  }
  
  private function getauthorslist($idpost) {
    $comusers = tcomusers::i($idpost);
    $html = $this->html;
    $args = targs::i();
    $perpage = 20;
    $total = $comusers->count;
    $from = $this->getfrom($perpage, $total);
    $items =array_slice(array_keys($comusers->items), $from, $perpage);
    $result = sprintf($html->h2->authorlisthead, $from, $from + count($items), $total);
    $result .= $html->authorheader();
    $args->adminurl = litepublisher::$site->url .$this->url . litepublisher::$site->q . "post=$idpost&id";
    $args->ip = '';
    foreach ($items as $id) {
      $args->id = $id;
      $args->add($comusers->items[$id]);
      $result .= $html->authoritem($args);
    }
    $result .= $html->authorfooter;
    
    $theme = ttheme::i();
    $result .= $theme->getpages($this->url, litepublisher::$urlmap->page, ceil($total/$perpage));
    return $result;
  }
  
  private function getsubscribed($authorid, $idpost) {
    $authorid = (int) $authorid;
    $comusers = tcomusers::i($idpost);
    if (!$comusers->itemexists($authorid))  return '';
    $html = $this->gethtml('moderator');
    $subscribers = tsubscribers::i();
    $args = targs::i();
    $post = tpost::i($idpost);
    $args->title = $post->title;
    $args->url = $post->url;
    $args->subscribed = $subscribers->exists($idpost, $authorid);
    return $this->html->subscribeitem($args);
  }
  
  public function processform() {
    switch ($this->name) {
      case 'comments':
      case 'hold':
      
      if (isset($_REQUEST['action'])) {
        switch ($_REQUEST['action']) {
          case 'reply':
          $post = tpost::i( (int) $_REQUEST['post']);
          $this->manager->reply($this->idget(), $post->id, $_POST['content']);
          @header("Location: litepublisher::$site->url$post->lastcommenturl");
          exit();
          
          case 'edit':
          $comments = tcomments::i($this->idpost);
          $comment = $comments->getcomment($this->idget());
          $comment->content = $_POST['content'];
          litepublisher::$classes->commentmanager->editrecent($comment->id, $comments->pid);
          break;
        }
      } else {
        $manager = $this->manager;
        $status = isset($_POST['approve']) ? 'approve' : (isset($_POST['hold']) ? 'hold' : 'delete');
        foreach ($_POST as $id => $value) {
          if (!is_numeric($id))  continue;
          $id = (int) $id;
          if ($status == 'delete') {
            $manager->delete($id);
          } else {
            $manager->setstatus($id,$this->idpost,  $status);
          }
        }
      }
      $result = $this->html->h2->successmoderated;
      break;
      
      case 'pingback':
      $pingbacks = tpingbacks::i($this->idpost);
      if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit') {
        extract($_POST);
        $pingbacks->edit($this->idget(), $title, $url);
      } else {
        $status = isset($_POST['approve']) ? 'approve' : (isset($_POST['hold']) ? 'hold' : 'delete');
        foreach ($_POST as $id => $value) {
          if (!is_numeric($id))  continue;
          $id = (int) $id;
          if ($status == 'delete') {
            $pingbacks->delete($id);
          } else {
            $pingbacks->setstatus($id, $status == 'approve');
          }
        }
      }
      $result = $this->html->h2->successmoderated;
      break;
      
      case 'authors':
      if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit') {
        $id = $this->idget();
        $comusers = tcomusers::i($this->idpost);
        if (!$comusers->itemexists($id)) return $this->notfound;
        $comusers->edit($id, $_POST['name'], $_POST['url'], $_POST['email'], $_POST['ip']);
        $subscribers = tsubscribers::i();
        $subscribed = $subscribers->getposts($id);
        $checked = array();
        foreach ($_POST as $idpost => $value) {
          if (!is_numeric($idpost))  continue;
          $checked [] = $idpost;
        }
        $unsub = array_diff($subscribed, $checked);
        if (count($unsub) > 0) {
          $subscribers->lock();
          foreach ($unsub as $idpost) {
            $subscribers->delete($idpost, $id);
          }
          $subscribers->unlock();
        }
        
        $result =  $this->html->h2->authoredited;
      }
      break;
    }
    
    litepublisher::$urlmap->clearcache();
    return $result;
  }
  
  private function getadminemail() {
    $profile = tprofile::i();
    if ($profile->mbox!= '') return $profile->mbox;
    return litepublisher::$options->fromemail;
  }
  
}//class
?>