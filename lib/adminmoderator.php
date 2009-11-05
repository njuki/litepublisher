<?php

class tadminmoderator extends tadminmenuitem {
  private $user;
  
  public static function instance() {
    return GetInstance(__class__);
  }
  
  private function getsubscribed($authorid) {
    global $options, $classes, $post;
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
  
  public function getcontent() {
    global $classes, $options, $urlmap;
    $result = '';
       $manager = $classes->commentmanager;
   
    switch ($this->name) {
      case 'moderator':
      case 'hold':
      if (isset($_GET['action']))    return $this->SingleModerate();

      $perpage = 20;
      $from = max(0, count($manager->items) - $urlmap->pagenumber * $perpage);
      if ($this->arg == 'hold') {
        $list = array_slice($manager->holditems, $from, $perpage, true);
      } else {
        $list = array_slice($manager->items, $from, $perpage, true);
      }
      eval('$s = "'. $html->listhead. '\n";');
      $result .= sprintf($s, $from, $from + count($list), count($manager->items));
      $result = $html->checkallscript;
      eval('$result .= "'. $html->tableheader . '\n";');
      $itemlist = $html->itemlist;
      foreach ($list as $id => $item) {
        //repair
        $comments = &TComments::instance($item['pid']);
        if (!isset($comments->items[$id])) {
          $manager->Delete($id);
          continue;
        }
        $comment = new TComment($comments);
        $comment->id = $id;
        $excerpt = TContentFilter::GetExcerpt($comment->content, 120);
        $onhold = $comment->status == 'hold' ? "checked='checked'" : '';
        eval('$result .="' . $itemlist . '\n";');
      }
      eval('$result .= "'. $html->tablefooter . '\n";');;
      $result = $this->FixCheckall($result);
      
      $tp = TTemplatePost::instance();
      $result .= $tp->PrintNaviPages('/admin/moderator/', $urlmap->page, ceil(count($manager->items)/$perpage));
      return $result;
      
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
  
  public function SingleModerate() {
    global $options;
    
    $id = (int) $_GET['commentid'];
    $manager = &TCommentManager::instance();
    if (!$manager->ItemExists($id)) return $this->notfound;
    if ($_GET['action'] == 'edit') return $this->EditComment($id);
    
    $comment = &TComments::GetComment($manager->items[$id]['pid'], $id);
    $result ='';
    switch ($_GET['action']) {
      case 'delete' :
      if  (isset($_GET['confirm']) && ($_GET['confirm'] == 1)) {
        $manager->Delete($id);
        eval('$result = "'. $html->successmoderated . '\n";');
        return $result;
      } else {
        eval('$result .= "'. $html->confirmform . '\n";');
        eval('$result .= "'. $html->info . '\n"; ');
        return $result;
      }
      break;
      
      case 'hold':
      $manager->SetStatus($id, 'hold');
      break;
      
      case 'approve':
      $manager->SetStatus($id, 'approved');
      break;
    }
    eval('$result = "'. $result .= $html->successmoderated . '\n";');
    eval('$result .= "'. $html->info . '\n"; ');
    return $result;
  }
  
  public function ProcessForm() {
    global $options, $urlmap;
    $html = &THtmlResource::instance();
    $html->section = 'moderator';
    $lang = &TLocal::instance();
    
    switch ($this->arg) {
      case null:
      case 'hold':
      if (!empty($_POST['approve'])) {
        $action = 'approve';
      } elseif (!empty($_POST['hold'])) {
        $action = 'hold';
      } elseif (!empty($_POST['delete'])) {
        $action = 'delete';
      } else {
        return '';
      }
      $manager = &TCommentManager::instance();
      $manager->Lock();
      foreach ($_POST as $id => $value) {
        if (!is_numeric($id))  continue;
        $id = (int) $id;
        if (!$manager->ItemExists($id)) continue;
        $comment = &TComments::GetComment($manager->items[$id]['pid'], $id);
        switch ($action) {
          case 'delete' :
          $manager->Delete($id);
          break;
          
          case 'hold':
          $manager->SetStatus($id, 'hold');
          break;
          
          case 'approve':
          $manager->SetStatus($id, 'approved');
          break;
        }
      }
      $manager->Unlock();
      eval('$result = "'. $html->successmoderated . '\n";');
      break;
      
      case 'authors':
      $authorid = $this->idget();
      $comusers = &tcomusers::instance();
      if (!$comusers->ItemExists($authorid)) return $this->notfound;
      if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'delete') &&!empty($_REQUEST['confirm'])  ) {
        $manager = &TCommentManager::instance();
        $manager->Lock();
        foreach ($manager->items as $id => $item) {
          if ($authorid == $item['uid']) $manager->Delete($id);
        }
        $comusers->Delete($authorid);
        $manager->Unlock();
        eval('$result = "'. $html->authordeleted . '\n";');
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