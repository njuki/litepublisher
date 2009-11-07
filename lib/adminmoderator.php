<?php

class tadminmoderator extends tadminmenuitem {
  private $user;
  
  public static function instance() {
    return GetInstance(__class__);
  }

protected function getmanager() }
global $classes;
return $classes->commentmanager;
}

  public function getcontent() {
    $result = '';
    switch ($this->name) {
      case 'moderate':
      case 'hold':
case 'pingback':

      if (isset($_GET['action'])) {
$id = $this->idget();
$action = $_GET['action'];
if (($action == 'delete') && !$this->confirmed) return $this->confirmdelete($id);
if (!$this->doaction($id, $action)) return $this->notfount;
$result .= $this->getactionresult($id, $action);
}

$result .= $this->getlist($this->name);
return $result;

            case 'authors':
      if (isset($_GET['action'])) {
$id = $this->idget();
$action = $_GET['action'];
switch ($action) {
case 'delete':
if (!$this->confirmed) return $this->confirmdeleteauthor($id);
if (!$this->deleteauthor($id)) return $this->notfount;
$result .= $this->html->h2->authordeleted; 
break;

case 'edit':
$result .= $this->editauthor($id);
}
}

$result .= $this->getauthorslist();
return $result;
    }

  }

private function reply($id) {
global $comment;
      $comment = $this->manager->getcomment($id);
return $this->html->replyform();
}  

private function getwherekind($kind) {
switch ($kind) {
case moderate':
return "status <> 'deleted' and pingback <> true";

case 'hold':
return "status = 'hold'";

case pingback':
return "status <> 'deleted' and pingback = true";
}
}

private function getlist($kind) {
    global $options, $urlmap, $comment;
$manager = $this->manager;
      $perpage = 20;
// подсчитать количество комментариев во всех случаях
if (dbversion ) {
$where = $this->getwherekind($kind);
$total = $manager->getcount($where);
} else {
switch ($kind) {
case moderate':
$total = $manager->count;
break;

case 'hold':
    $holditems = array();
    foreach($manager->items as $id => $item) {
      if (!empty($item['status']) && ($item['status'] == 'hold')) {
        $holditems[$id] = $item;
      }
    }
$total = count($holditems);
break;
  
case pingback':
    $pingbacks = array();
    foreach($manager->items as $id => $item) {
      if (!empty($item['type']) && ($item['type'] == 'pingback')) {
        $pingbacks[$id] = $item;
      }
    }
$total = count($pingbacks);
break;
}
}

      $from = max(0, $total - $urlmap->page * $perpage);

if (dbversion ) {
//$where = $this->getwherekind($kind);
        $list = $manager->getitems($where,  $from, $perpage);
} else {
switch ($kind) {
case moderate':
        $list = array_slice($manager->items, $from, $perpage, true);
break;

case 'hold':
        $list = array_slice($holditems, $from, $perpage, true);
break;

case 'pingback':
        $list = array_slice($pingbacks, $from, $perpage, true);
break;
      }
}

$html = $this->html;
      $result .= sprintf($html->h2->listhead, $from, $from + count($list), $total);
      $result = $html->checkallscript;
$result .= $html->tableheader();
$args->adminurl = $options->url . $this->url . $options->q. 'id';
      foreach ($list as $id => $data) {
//трюк - в db уже готовые комменты, а на файлах только id;
if (dbversion) {
$comment->data = $data;
} else {
        //repair
        $comments = &TComments::instance($data['pid']);
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
      $result .= $tp->PrintNaviPages($this->url, $urlmap->page, ceil($total/$perpage));
      return $result;
}  

  
  private function doaction($id, $action) {
$manager = $this->manager;
    if (!$manager->itemexists($id)) return false;
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
case 'edit': 
$this->editcomment($id);
break;

case 'reply': 
$this->reply($id);
break;
    }
    return true;
  }
  
private function getactionresult($id, $action) {
$result = $this->html->h2->successmoderated;
    switch ($action) {
      case 'delete' :
return $result;

            case 'hold':
     case 'approve':
$result .= $this->getinfo($id);
return $result;

case 'edit': 
return $this->editcomment($id);

case 'reply': 
return $this->editcomment($id);
    }
}

private function getinfo($id) {
global $comment;
$manager = $this->manager;
    $comment = $manager->getcomment($id);
return $this->html->info();
}

private function confirmdelete($id) {
$result = $this->getconfirmform($id, $this->lang->confirmdelete);
$result .= $this->getinfo($id);
return $result;
}

private function getconfirmform($id, $confirm) {
global $options;
$args = new targs();
$args->id = $id;
$args->action = 'delete';
$args->adminurl = $options->url . $this->url . $options->q . 'id';
$args->confirm = $confirm;
return $this->html->confirmform($args);
      }

private function confirmdeleteauthor($id) {
return $this->getconfirmform($id, $this->lang->authorconfirmdelete);
}

  private function doauthoraction($id, $action) {
      $comusers = tcomusers::instance();
    if (!$comusers->itemexists($id)) return false;
    switch ($action) {
      case 'delete' :
  private function deleteauthor($uid, $action) {
      $comusers = tcomusers::instance();
    if (!$comusers->itemexists($uid)) return false;
$manager = $this->manager;
if (dbversion) {
$manager->db->delete("author = $uid");
        $comusers->delete($uid);
} else {
        $manager->lock();
        foreach ($manager->items as $id => $item) {
          if ($uid == $item['uid']) $manager->Delete($id);
        }
        $comusers->delete($uid);
        $manager->unlock();
}
    return true;
  }

private function editauthor($id) {
$args = new targs();
if ($id == 0) {
        $args->id = 0;
        $args->name = '';
        $args->email = '';
        $args->url = '';
      $args->subscribed '';
} else {
$comusers = tcomusers::instance();
if (!$comusers->itemexists($id)) return $this->notfound;
$author = $comusers->getitem($id);
$args->id = $id;
$args->name = $author['name'];
$args->url = $author['url'];
$args->email = $author['email'];
      $args->subscribed = $this->getsubscribed($id);
}
return $this->html->authorform($args);
}

private function getauthorslist() {
$comusers = tcomusers::instance();
$html = $this->html;
$args = new targs();
$result = html->authorheader($args);
if (dbversion) {
} else {
      foreach ($comusers->items as $id => $item) {
        if (is_array($item['ip'])) $ip = implode('; ', $item['ip']);
$result .= $html->authoritem($args);
      }
}
$result .= $html->authorfooter;
      $result = $this->FixCheckall($result);
return $result;
     }

  private function getsubscribed($authorid) {
    global $options, $post;
$authorid = (int) $authorid;
    $comusers = tcomusers::instance();
    if (!$comusers->itemexists($authorid))  return '';
$html = $this->gethtml('moderator');
    $result = $html->checkallscript;
$manager = $this->manager;
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
  
  public function processform() {
    global $options, $urlmap;
$manager = $this->manager;
/*
обрабатываются все формы, в том числе и потдверждения. Потдверждение требуется только для удаления, для всего остального (одобрить, задержать) не требуется, поэтому простые клики по ссылки приведет сразу к обработке комментария.
*/    

    switch ($this->name) {
      case 'moderate':
      case 'hold':
case 'pingback':

switch ($_REQEST['action']) {

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

      case 'reply':
      $email = $this->getadminemail();
      $site = $options->url . $options->home;
      $profile = tprofile::instance();
      $comusers = tccomusers ::instance();
      $authorid = $comusers->add($profile->nick, $email, $site);
$post = tpost::instance($manager->items[$id]['pid']);
      $manager->addcomment($post->id, $authorid, $_POST['content']);
    $posturl = $post->haspages ? rtrim($post->url, '/') . "/page/$post->commentspages/" : $post->url;
      @header("Location: $options->url$posturl");
      exit();
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
    }
    
    $urlmap->ClearCache();
    return $result;
  }
  
  private function getadminemail() {
    global $options;
    $profile = &TProfile::instance();
    if ($profile->mbox!= '') return $profile->mbox;
    return $options->fromemail;
  }
  
  public function EditComment($id) {
    global $options;
$manager = $this->manager;
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