<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminmoderator extends tadminmenu {
  private $user;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function getmanager() {
    global $classes;
    return $classes->commentmanager;
  }

private function getidpost() {
return isset($_REQUEST['post']) ? (int) $_REQUEST['post'] : 0;
}
  
  public function getcontent() {
    $result = '';
    switch ($this->name) {
      case 'comments':
      case 'hold':
      
      if ($action = $this->action) {
        $id = $this->idget();
        $comments = tcomments::instance($this->idpost);
        if (!$comments->itemexists($id)) return $this->notfound;
        switch($action) {
          case 'delete':
          if(!$this->confirmed) return $this->confirmdelete($id);
            $this->manager->delete($id, $this->idpost);
    $result .= $this->html->h2->successmoderated;
          break;
          
          case 'hold':
          $this->manager->setstatus($this->idpost, $id, 'hold');
        $result .= $this->moderated($id, $this->idpost);
          break;
          
          case 'approve':
          $this->manager->setstatus($this->idpost, $id, 'approved');
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
$pingbacks = tpingbacks::instance($this->idpost);
        if (!$pingbacks->itemexists($id)) return $this->notfound;
        switch($action) {
          case 'delete':
          if(!$this->confirmed) return $this->confirmdelete($id, $this->idpost);
$pingbacks->delete($id);
    $result .= $this->html->h2->successmoderated;
          break;
          
          case 'hold':
$pingbacks->setstatus($id, false);
    $result .= $this->html->h2->successmoderated;
          break;
          
          case 'approve':
$pingbacks->setstatus($id, true);
    $result .= $this->html->h2->successmoderated;
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
      if ($action = $this->action) {
        $id = $this->idget();
        switch ($action) {
          case 'delete':
          if (!$this->confirmed) return $this->confirmdeleteauthor($id, $this->idpost);
$comments = tcomments::instance($this->idpost);
$comments->deleteauthor($id);
          $result .= $this->html->h2->authordeleted;
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
global $comment;
    $comment = tcomments::getcomment($idpost, $id);
    $args = targs::instance();
    $args->content = $comment->content;
    $args->adminurl =$this->adminurl . "=$id&post=$idpost&action";
$result = $this->html->info($args);
$result .= $this->html->editform($args);
return $result;
  }

  private function reply($id, $idpost) {
    global $comment;
    $comment = tcomments::instance($idpost, $id);
$args = targs::instance();
    $args->adminurl =$this->adminurl . "=$id&post=$idpost&action";
$result = $this->html->info($args);
$result .= $this->html->replyform();
return $result;
  }
  
  private function getlist($status, $idpost) {
    global $options, $urlmap, $comment;
    $result = '';
$comments = tcomments::instance($idpost);
if ($status == 'hold') $comments = $comments->hold;
    $perpage = 20;
    // подсчитать количество комментариев во всех случаях
    $total = $comments->count;
    $from = max(0, $total - $urlmap->page * $perpage);
        $list = array_slice(array_keys($comments->items), $from, $perpage);
    $html = $this->html;
    $result .= sprintf($html->h2->listhead, $from, $from + count($list), $total);
    $result .= $html->checkallscript;
    $result .= $html->tableheader();
    $args = targs::instance();
    $args->adminurl = $this->adminurl ."post=$idpost&id";
        $comment = new TComment($comments);
    foreach ($list as $id) {
        $comment->id = $id;
      $args->id = $id;
      $args->excerpt = tcontentfilter::getexcerpt($comment->content, 120);
      $args->onhold = $comment->status == 'hold';
      $args->email = $comment->email == '' ? '' : "<a href='mailto:$comment->email'>$comment->email</a>";
      $args->website =$comment->website == '' ? '' : "<a href='$comment->website'>$comment->website</a>";
      $result .=$html->itemlist($args);
    }
    $result .= $html->tablefooter();
    $result = $this->FixCheckall($result);
    
    $theme = ttheme::instance();
    $result .= $theme->getpages($this->url, $urlmap->page, ceil($total/$perpage));
    return $result;
  }

private function getpostlist($status) {
    global $options, $urlmap, $post;
    $result = '';
    $posts = tposts::instance();
    $perpage = 20;
    $count = $posts->count;
    $from = max(0, $count - $urlmap->page * $perpage);
      $items = array_slice($posts->items, $from, $perpage, true);
      $items = array_reverse (array_keys($items));

    $html = $this->html;
    $head =sprintf($html->h2->postscount, $from, $from + count($items), $count);
    $args = targs::instance();
    $args->adminurl = $options->url .$this->url . $options->q . 'post';
    foreach ($items  as $id ) {
      $post = tpost::instance($id);
      $result .= $html->postitem($args);
$result .= "\n";
    }
    $result = sprintf($html->postlist, $result);
$result = $head . $result;
    $result = str_replace("'", '"', $result);
    
    $theme = ttheme::instance();
    $result .= $theme->getpages('$this->url, $urlmap->page, ceil($count/$perpage));
    return $result;
}

  private function getpingbackslist($idpost) {
    global $options, $urlmap;
    $result = '';
$pingbacks = tpingbacks::instance($idpost);
    $perpage = 20;
    $total = $pingbacks->getcount();
    $from = max(0, $total - $urlmap->page * $perpage);
        $list = array_slice(array_keys($pingbacks->items), $from, $perpage);
    $html = $this->html;
    $result .= sprintf($html->h2->pingbackhead, $from, $from + count($items), $total);
    $result .= $html->checkallscript;
    $result .= $html->pingbackheader();
    $args = targs::instance();
    $args->adminurl = $options->url .$this->url . $options->q . "post=$idpost&id";
$post = tpost::instance($idpost);
$args->posttitle =$post->title;
$args->postlink = $post->link;
    foreach ($items as $id) {
$item = $pingbacks->items[$id];
      $args->add($item);
$args->id = $id;
      $args->website = sprintf("<a href='%s'>%s</a>", $item['url']);
$args->localstatus = tlocal::$data['commentstatus'][$item['status']];
$args->date = tlocal::date(strtotime($pingback->posted));
      $result .=$html->pingbackitem($args);
    }
    $result .= $html->tablefooter();
    $result = $this->FixCheckall($result);
    
    $theme = ttheme::instance();
    $result .= $theme->getpages($this->url, $urlmap->page, ceil($total/$perpage));
    return $result;
  }

  private function editpingback($id, $idpost) {
$pingbacks = tpingbacks::instance($idpost);
    $args = targs::instance();
$args->add($pingbacks->getitem($id));
return $this->html->pingbackedit($args);
  }

  private function moderated($id, $idpost) {
    $result = $this->html->h2->successmoderated;
$result .= $this->getinfo($id, $idpost);
    return $result;
  }
  
  private function getinfo($id, $idpost) {
    global $comment;
if (!isset($comment)) $comment = tcomments::getcomment($idpost, $id);
    $args = targs::instance();
    $args->adminurl =$this->adminurl . "=$id&post=$idpost&action";
    return $this->html->info($args);
  }
  
  private function confirmdelete($id, $idpost) {
    $result = $this->getconfirmform($id, $this->lang->confirmdelete);
    $result .= $this->getinfo($id, $idpost);
    return $result;
  }
  
  private function getconfirmform($id, $idpost, $confirm) {
    global $options;
    $args = targs::instance();
    $args->id = "$id&post=$idpost";
    $args->action = 'delete';
    $args->adminurl = $options->url . $this->url . $options->q . 'id';
    $args->confirm = $confirm;
    return $this->html->confirmform($args);
  }
  
  private function confirmdeleteauthor($id, $idpost) {
    return $this->getconfirmform($id, $idpost, $this->lang->authorconfirmdelete);
  }
  
  private function editauthor($id, $idpost) {
    $args = targs::instance();
    if ($id == 0) {
      $args->id = "0&post=$idpost";
      $args->name = '';
      $args->email = '';
      $args->url = '';
      $args->subscribed = '';
    } else {
      $comusers = tcomusers::instance($idpost);
      if (!$comusers->itemexists($id)) return $this->notfound;
$args->add($comusers->getitem($id));
$args->id = "id&post=$idpost";
      $args->subscribed = $this->getsubscribed($id, $idpost);
    }
    return $this->html->authorform($args);
  }
  
  private function getauthorslist($idpost) {
    global $urlmap;
    $comusers = tcomusers::instance($idpost);
    $args = targs::instance();
    $perpage = 20;
    $total = $comusers->count;
    $from = max(0, $total - $urlmap->page * $perpage);
$items =array_slice(array_keys($comusers->items), $from, $perpage);
    $result = sprintf($html->h2->authorlisthead, $from, $from + count($items), $total);
    $result .= $html->authorheader();
    $args->adminurl = $this->adminurl;
    foreach ($items as $id) {
$args->id = "$id&post=$idpost";
        $args->add($comusers->items[$id]);
      $result .= $html->authoritem($args);
    }
    $result .= $html->authorfooter;
    
    $theme = ttheme::instance();
    $result .= $theme->getpages($this->url, $urlmap->page, ceil($total/$perpage));
    return $result;
  }
  
  private function getsubscribed($authorid, $idpost) {
    global $options, $post;
    $authorid = (int) $authorid;
    $comusers = tcomusers::instance($idpost);
    if (!$comusers->itemexists($authorid))  return '';
    $html = $this->gethtml('moderator');
    $subscribers = tsubscribers::instance();
        $args = targs::instance();
      $post = tpost::instance($idpost);
      $args->subscribed = $subscribers->subscribed($idpost, $authorid);
return $this->html->subscribeitem($args);
  }
  
  public function processform() {
    global $options, $urlmap;
    $manager = $this->manager;
$idpost = $this->idpost;
    switch ($this->name) {
      case 'comments':
$instance = tcomments::instance($idpost);
break;

      case 'hold':
$instance = tcomments::instance($idpost)->hold;
break;

      case 'pingback':
$instance = tpingbacks::instance($idpost);
break;

case 'author':
$instance = tcomusers::instance($idpost);
break;
}
      
      $action = $_REQEST['action'];
      switch ($action) {
        case 'reply':
        $email = $this->getadminemail();
        $site = $options->url . $options->home;
        $profile = tprofile::instance();
        $comusers = tccomusers ::instance();
        $authorid = $comusers->add($profile->nick, $email, $site);
        $post = tpost::instance($idpost);
        $manager->addcomment($post->id, $authorid, $_POST['content']);
        $posturl = $post->haspages ? rtrim($post->url, '/') . "/page/$post->commentpages/" : $post->url;
        @header("Location: $options->url$posturl");
        exit();
        
        case 'edit':
        $comment = $manager->getcomment($this->idget());
        $comment->content = $_POST['content'];
        break;
        
        default:
        $manager->Lock();
        foreach ($_POST as $id => $value) {
          if (!is_numeric($id))  continue;
          $id = (int) $id;
          $this->doaction($id, $action);
        }
        $manager->unlock();
        $result = $this->html->h2->successmoderated;
      }
      break;
      
      case 'authors':
      if (isset($_REQUEST['action'])  && ($_REQUEST['action'] == 'edit')) {
        $id = $this->idget();
        $comusers = tcomusers::instance();
        if (!$comusers->itemexists($id)) return $this->notfound;
        $comusers->edit($id, $_POST['name'], $_POST['url'], $_POST['email'], $_POST['ip']);
        $subscribers = tsubscribers::instance();
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
        
        $result =  $html->h2->authoredited;
      }
      break;
    }
    
    $urlmap->clearcache();
    return $result;
  }
  
  private function getadminemail() {
    global $options;
    $profile = tprofile::instance();
    if ($profile->mbox!= '') return $profile->mbox;
    return $options->fromemail;
  }
  
}//class
?>