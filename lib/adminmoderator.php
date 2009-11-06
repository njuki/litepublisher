<?php

class tadminmoderator extends tadminmenuitem {
  private $user;
  
  public static function instance() {
    return GetInstance(__class__);
  }
  
  private function getsubscribed($authorid) {
    global $options, $classes, $post;
$authorid = (int) $authorid;
    $comusers = tcomusers::instance();
    if (!$comusers->itemexists($authorid))  return '';
$html = $this->gethtml('moderator');
    $result = $html->checkallscript;
$manager = $classes->commentmanager;
if (dbversion) {
$posted = $manager->db->res2array($manager->db->query("select DISTINCT post from $manager->thistable where author = $author"));
} else { 
$posted = array();
foreach ($manager->items as $id => $item) {
if ($item['uid'] == $authorid) {
if (!in_array($item['pid'], $posted)) $posted[] =$item['pid'];
}
}
}

    $subscribers = $tsubscribers::instance();
    $subscribed = $subscribers->getposts($authorid);
    
$args = new targs();
    foreach ($posted as $idpost) {
$post = tpost::instance($idpost);
      $args->subscribed = in_array($idpost, $subscribed);
$result .= $html->subscribeitem($args);
    }
    
    return $this->FixCheckall($result);
  }

private function getcomments($kind) {
global $comment;

      $result .= sprintf($html->h2->listhead, $from, $from + count($list), count($manager->items));
      $result = $html->checkallscript;
$result .= $html->tableheader();
$args->adminurl = $options->url . $this->url . . $options->q. 'id';
      foreach ($list as $id => $item) {
if (dbversion) {
$comment->data = $item;
} else {
        //repair
        $comments = &TComments::instance($item['pid']);
        if (!isset($comments->items[$id])) {
          $manager->delete($id);
          continue;
        }

        $comment = new TComment($comments);
        $comment->id = $id;
}
$args->id = $comment->id;
        $args->excerpt = TContentFilter::GetExcerpt($comment->content, 120);
        $args->onhold = $comment->status == 'hold';
$args->email = $comment->email == '' ? '' "<a href='mailto:$comment->email'>$comment->email</a>";
$args->website =$comment->website == '' ? '' : "<a href='$comment->website'>$comment->website</a>";
$result .=$html->itemlist($args);
      }
$result .= $html->tablefooter;
      $result = $this->FixCheckall($result);
      
      $tp = TTemplatePost::instance();
      $result .= $tp->PrintNaviPages('/admin/moderator/', $urlmap->page, ceil(count($manager->items)/$perpage));
      return $result;
}  

  public function getcontent() {
    global $classes, $options, $urlmap, $comment;
    $result = '';
       $manager = $classes->commentmanager;
   $html = $this->html;
    switch ($this->name) {
      case 'moderator':
      case 'hold':
case 'pingback':

      if (isset($_GET['action']))    return $this->dosingle($this->idget(), $_GET['action']);

      $perpage = 20;
      $from = max(0, $manager->count - $urlmap->page * $perpage);
      if ($this->name == 'hold') {
        $list = array_slice($manager->holditems, $from, $perpage, true);
      } elseif (dbversion) {
        $list = $manager->getitems("status <> 'deleted'", $from, $perpage);
} else {
        $list = array_slice($manager->items, $from, $perpage, true);
      }

      
      case 'authors':
      $comusers = tcomusers::instance();
      $id = $this->idget();
      if ($comusers->itemexists($id)) {
$author = $comusers->getitem($id);
$args->id = $id;
$args->name = $author['name'];
$args->url = $author['url'];
$args->email = $author['email'];
        if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'delete')) {
          $result .= $html->authorconfirmdelete($id, $name, $url, $email);
          return str_replace("'", '"', $result);
        }
      } else {
        $args->id = 0;
        $args->name = '';
        $args->email = '';
        $args->url = '';
      }
      $args->subscribed = $this->getsubscribed($id);
$result .= $this->html->authorheader($args);

      foreach ($comusers->items as $id => $item) {
        if (is_array($item['ip'])) $ip = implode('; ', $item['ip']);
        eval('$result .= "'. $html->authoritem . '\n";');
      }
      eval('$result .= "'. $html->authorfooter . '\n";');;
      $result = $this->FixCheckall($result);
      break;
      
      case 'reply':
      $id = $this->idget();
      if (!$manager ->ItemExists($id))return $this->notfound();
      $comment = &$manager->Getcomment($id);
      eval('$result .= "'. $html->replyform . '\n";');
      break;
    }
    
    return $result;
  }
  
  public function dosingle($id, $action) {
    global $classes, $options, $comment;
        $manager = $classes->commentmanager;
    if (!$manager->itemexists($id)) return $this->notfound;

    $comment = $manager->getcomment($id);
    switch ($action) {
case 'edit': 
return $this->editcomment($id);

      case 'delete' :
      if  ($this->confirmed) {
        $manager->delete($id);
return $this->html->h2->successmoderated ;
      } else {
$args = new targs();
$args->action = 'delete';
$args->adminurl = $options->url . $this->url . $options->q . 'id';
$args->id = $id;
$args->confirm = $this->lang->confirmdelete;
$result = $html->confirmform($args);
$result .= $html->info();
        return $result;
      }

            case 'hold':
      $manager->setstatus($id, 'hold');
      break;
      
      case 'approve':
      $manager->setstatus($id, 'approved');
      break;
    }

$result = $this->html->h2->successmoderated;
$result .= $this->html->info();
    return $result;
  }
  
  public function processform() {
    global $classes, $options, $urlmap;
      $manager = $classes->commentmanager;
    
    switch ($this->name) {
      case 'moderate':
      case 'hold':
case 'pingback':
      if (!empty($_POST['approve'])) {
        $action = 'approve';
      } elseif (!empty($_POST['hold'])) {
        $action = 'hold';
      } elseif (!empty($_POST['delete'])) {
        $action = 'delete';
      } else {
        return '';
      }

      $manager->Lock();
      foreach ($_POST as $id => $value) {
        if (!is_numeric($id))  continue;
        $id = (int) $id;
        if (!$manager->itemexists($id)) continue;
        $comment = $manager->getcomment($id);
        switch ($action) {
          case 'delete' :
          $manager->delete($id);
          break;
          
          case 'hold':
          $manager->setstatus($id, 'hold');
          break;
          
          case 'approve':
          $manager->setstatus($id, 'approved');
          break;
        }
      }
      $manager->unlock();
$result = $this->html->h2->successmoderated;
      break;
      
      case 'authors':
      $authorid = $this->idget();
      $comusers = tcomusers::instance();
      if (!$comusers->itemexists($authorid)) return $this->notfound;
      if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'delete') &&!empty($_REQUEST['confirm'])  ) {
if (dbversion) {
$manager->db->delete("author = $author");
        $comusers->delete($authorid);
} else {
        $manager->lock();
        foreach ($manager->items as $id => $item) {
          if ($authorid == $item['uid']) $manager->Delete($id);
        }
        $comusers->delete($authorid);
        $manager->unlock();
}
return $html->h2->authordeleted;
      } else {
        $comusers->items[$authorid]['name'] = $_POST['name'];
        $comusers->items[$authorid]['url'] = $_POST['url'];
        $comusers->items[$authorid]['email'] = $_POST['email'];
        $comusers->items[$authorid]['subscribe'] = array();
        foreach ($_POST as $postid => $value) {
          if (!is_numeric($postid))  continue;
          $comusers->items[$authorid]['subscribe'][]  = (int) $postid;
        }
        $comusers->Save();
        eval('$result = "'. $html->authoredited . '\n";');
      }
      break;
      
      case 'reply':
      $id = !empty($_GET['id']) ? (int) $_GET['id'] : (!empty($_POST['id']) ? (int)$_POST['id'] : 0);
      $manager = &TCommentManager::instance();
      if (!$manager->ItemExists($id)) return $this->notfound;
      $email = $this->GetAdminEmail();
      $site = $options->url . $options->home;
      $profile = &TProfile::instance();
      $comusers = &tcomusers ::instance();
      $authorid = $comusers->Add($profile->nick, $email, $site);
$post = tpost::instance($manager->items[$id]['pid']);
      $manager->AddToPost($post->id, $authorid, $_POST['content']);
    $posturl = $post->haspages ? rtrim($post->url, '/') . "/page/$post->commentspages/" : $post->url;
      @header("Location: $options->url$posturl");
      exit();
      
    }
    
    $urlmap->ClearCache();
    return $result;
  }
  
  private function GetAdminEmail() {
    global $options;
    $profile = &TProfile::instance();
    if ($profile->mbox!= '') return $profile->mbox;
    return $options->fromemail;
  }
  
  public function EditComment($id) {
    global $options;
    $manager =&TCommentManager::instance();
    $comment = $manager->GetComment($id);
    if (isset($_POST['submit'])) {
      $comment->content = $_POST['content'];
      //$comment->Save();
    }
    $content = $this->ContentToForm($comment->content);
    $result = '';
    $html = &THtmlResource::instance();
    $html->section = 'moderator';
    $lang = &TLocal::instance();
    
    eval('$result .= "'. $html->info . '\n";');
    eval('$result .= "'. $html->editform . '\n";');
    return $result;
  }
  
}//class
?>