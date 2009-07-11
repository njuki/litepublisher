<?php

class TAdminModerator extends TAdminPage {
  private $user;
  
  public static function &Instance() {
    return GetInstance(__class__);
  }
  
  protected function CreateData() {
    parent::CreateData();
    $this->basename = 'moderator';
  }
  
  private function GetSubscribed($authorid) {
    global $Options;
    $result = '';
    $authors = &TCommentUsers::Instance();
    $author = $authors->items[$authorid];
    $manager = &TCommentManager::Instance();
    $list = array();
    foreach ($manager->items as $id => $item) {
      if ($authorid != $item['uid'])  continue;
      $pid = $item['pid'];
      if (isset($list[$pid]))  {
        $list[$pid]['count']++;
      } else {
        $list[$pid] = array('count' => 1);
      }
      $list[$pid]['subscribed'] = in_array($pid, $author['subscribe']);
    }
    
    $checked = "checked='checked'";
    $html = &THtmlResource::Instance();
    $html->section = $this->basename;
    $lang = &TLocal::Instance();
    $subcribeitem = $html->subscribeitem;
    foreach ($list as $id => $item) {
      $subscribed = $item['subscribed'] ? $checked : '';
      $post = &TPost::Instance($id);
      eval('$result .= "'. $subcribeitem . '\n";');
    }
    
    return $result;
  }
  
  public function Getcontent() {
    global $Options, $Urlmap;
    $result = '';
    $html = &THtmlResource::Instance();
    $html->section = 'moderator';
    $lang = &TLocal::Instance();
    
    $checked = "checked='checked'";
    $CommentManager = &TCommentManager::Instance();
    
    switch ($this->arg) {
      case null:
      case 'hold':
      if (!empty($_GET['action']))    return $this->SingleModerate();
      
      $from = max(0, count($CommentManager->items) - $Urlmap->pagenumber * 100);
      if ($this->arg == 'hold') {
        $list = array_slice($CommentManager->holditems, $from, 100, true);
      } else {
        $list = array_slice($CommentManager->items, $from, 100, true);
      }
      eval('$s = "'. $html->listhead. '\n";');
      $result .= sprintf($s, $from, $from + count($list), count($CommentManager->items));
      $result = $html->checkallscript;
      eval('$result .= "'. $html->tableheader . '\n";');
      $itemlist = $html->itemlist;
      foreach ($list as $id => $item) {
        //repair
        $comments = &TComments::Instance($item['pid']);
        if (!isset($comments->items[$id])) {
          $CommentManager->Delete($id);
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
      
      $TemplatePost = &TTemplatePost::Instance();
      $result .= $TemplatePost ->PrintNaviPages('/admin/moderator/', $Urlmap->pagenumber, ceil(count($CommentManager->items)/100));
      return $result;
      
      case 'authors':
      $authors = &TCommentUsers::Instance();
      $id = !empty($_GET['id']) ? (int) $_GET['id'] : (!empty($_POST['id']) ? (int)$_POST['id'] : 0);
      if ($authors->ItemExists($id)) {
        if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'delete')) {
          eval('$result .= "'. $html->authorconfirmdelete . '\n";');
          return str_replace("'", '"', $result);
        }
        extract($authors->items[$id]);
        $subscribed = $this->GetSubscribed($id);
      } else {
        $id = 0;
        $name = '';
        $email = '';
        $url = '';
        $subscribed = '';
      }
      eval('$result .= "' . $html->authorheader . '\n";');
      foreach ($authors->items as $id => $item) {
        if (is_array($item['ip'])) $ip = implode('; ', $item['ip']);
        eval('$result .= "'. $html->authoritem . '\n";');
      }
      eval('$result .= "'. $html->authorfooter . '\n";');;
      $result = $this->FixCheckall($result);
      break;
      
      case 'reply':
      $id = $this->idget();
      if (!$CommentManager ->ItemExists($id))return $this->notfound();
      $comment = &$CommentManager->Getcomment($id);
      eval('$result .= "'. $html->replyform . '\n";');
      break;
    }
    
    return $result;
  }
  
  public function SingleModerate() {
    global $Options;
    $html = &THtmlResource::Instance();
    $html->section = 'moderator';
    $lang = &TLocal::Instance();
    
    $id = (int) $_GET['commentid'];
    $CommentManager = &TCommentManager::Instance();
    if (!$CommentManager->ItemExists($id)) return $this->notfound;
    if ($_GET['action'] == 'edit') return $this->EditComment($id);
    
    $comment = &TComments::GetComment($CommentManager->items[$id]['pid'], $id);
    $result ='';
    switch ($_GET['action']) {
      case 'delete' :
      if  (isset($_GET['confirm']) && ($_GET['confirm'] == 1)) {
        $CommentManager->Delete($id);
        eval('$result = "'. $html->successmoderated . '\n";');
        return $result;
      } else {
        eval('$result .= "'. $html->confirmform . '\n";');
        eval('$result .= "'. $html->info . '\n"; ');
        return $result;
      }
      break;
      
      case 'hold':
      $CommentManager->SetStatus($id, 'hold');
      break;
      
      case 'approve':
      $CommentManager->SetStatus($id, 'approved');
      break;
    }
    eval('$result = "'. $result .= $html->successmoderated . '\n";');
    eval('$result .= "'. $html->info . '\n"; ');
    return $result;
  }
  
  public function ProcessForm() {
    global $Options, $Urlmap;
    $html = &THtmlResource::Instance();
    $html->section = 'moderator';
    $lang = &TLocal::Instance();
    
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
      $CommentManager = &TCommentManager::Instance();
      $CommentManager->Lock();
      foreach ($_POST as $id => $value) {
        if (!is_numeric($id))  continue;
        $id = (int) $id;
        if (!$CommentManager->ItemExists($id)) continue;
        $comment = &TComments::GetComment($CommentManager->items[$id]['pid'], $id);
        switch ($action) {
          case 'delete' :
          $CommentManager->Delete($id);
          break;
          
          case 'hold':
          $CommentManager->SetStatus($id, 'hold');
          break;
          
          case 'approve':
          $CommentManager->SetStatus($id, 'approved');
          break;
        }
      }
      $CommentManager->Unlock();
      eval('$result = "'. $html->successmoderated . '\n";');
      break;
      
      case 'authors':
      $authorid = $this->idget();
      $authors = &TCommentUsers::Instance();
      if (!$authors->ItemExists($authorid)) return $this->notfound;
      if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'delete') &&!empty($_REQUEST['confirm'])  ) {
        $CommentManager = &TCommentManager::Instance();
        $CommentManager->Lock();
        foreach ($CommentManager->items as $id => $item) {
          if ($authorid == $item['uid']) $CommentManager->Delete($id);
        }
        $authors->Delete($authorid);
        $CommentManager->Unlock();
        eval('$result = "'. $html->authordeleted . '\n";');
      } else {
        $authors->items[$authorid]['name'] = $_POST['name'];
        $authors->items[$authorid]['url'] = $_POST['url'];
        $authors->items[$authorid]['email'] = $_POST['email'];
        $authors->items[$authorid]['subscribe'] = array();
        foreach ($_POST as $postid => $value) {
          if (!is_numeric($postid))  continue;
          $authors->items[$authorid]['subscribe'][]  = (int) $postid;
        }
        $authors->Save();
        eval('$result = "'. $html->authoredited . '\n";');
      }
      break;
      
      case 'reply':
      $id = !empty($_GET['id']) ? (int) $_GET['id'] : (!empty($_POST['id']) ? (int)$_POST['id'] : 0);
      $manager = &TCommentManager::Instance();
      if (!$manager->ItemExists($id)) return $this->notfound;
      $email = $this->GetAdminEmail();
      $site = $Options->url . $Options->home;
      $profile = &TProfile::Instance();
      $authors = &TCommentUsers ::Instance();
      $authorid = $authors->Add($profile->nick, $email, $site);
      $post = &TPost::Instance($manager->items[$id]['pid']);
      $manager->AddToPost($post, $authorid, $_POST['content']);
      @header("Location: $Options->url$post->url");
      exit();
      
    }
    
    $Urlmap->ClearCache();
    return $result;
  }
  
  private function GetAdminEmail() {
    global $Options;
    $profile = &TProfile::Instance();
    if ($profile->mbox!= '') return $profile->mbox;
    return $Options->fromemail;
  }
  
  public function EditComment($id) {
    global $Options;
    $CommentManager =&TCommentManager::Instance();
    $comment = &$CommentManager->GetComment($id);
    if (isset($_POST['submit'])) {
      $comment->content = $_POST['content'];
      //$comment->Save();
    }
    $content = $this->ContentToForm($comment->content);
    $result = '';
    $html = &THtmlResource::Instance();
    $html->section = 'moderator';
    $lang = &TLocal::Instance();
    
    eval('$result .= "'. $html->info . '\n";');
    eval('$result .= "'. $html->editform . '\n";');
    return $result;
  }
  
}//class
?>