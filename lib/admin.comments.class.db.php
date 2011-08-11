<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminmoderator extends tadmincommoncomments {
  
  public static function instance($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function getcontent() {
    $result = '';
    $lang = $this->lang;
    $html = $this->html;
    
    switch ($this->name) {
      case 'comments':
      case 'hold':
      
      if ($action = $this->action) {
        $id = $this->idget();
        $comments = tcomments::instance();
        if (!$comments->itemexists($id)) return $this->notfound;
        switch($action) {
          case 'delete':
          if(!$this->confirmed) return $this->confirmdelete($id);
          $this->manager->delete($id, 0);
          $result .= $html->h2->successmoderated;
          break;
          
          case 'hold':
          $this->manager->setstatus($id, 0, 'hold');
          $result .= $this->moderated($id);
          break;
          
          case 'approve':
          $this->manager->setstatus($id, 0, 'approved');
          $result .= $this->moderated($id);
          break;
          
          case 'edit':
          $result .= $this->editcomment($id);
          break;
          
          case 'reply':
          $result .= $this->reply($id);
          break;
        }
      }
      
      $result .= $this->getlist($this->name);
      return $result;
      
      case 'pingback':
      if ($action = $this->action) {
        $id = $this->idget();
        $pingbacks = tpingbacks::instance();
        if (!$pingbacks->itemexists($id)) return $this->notfound;
        switch($action) {
          case 'delete':
          if(!$this->confirmed) return $this->confirmdelete($id);
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
          $result .= $this->editpingback($id);
          break;
        }
      }
      $result .= $this->getpingbackslist();
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
      $rss = trssholdcomments::instance();
      $args = targs::instance();
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
    $args = targs::instance();
    $args->content = $comment->content;
    $args->adminurl =$this->adminurl . "=$id&action";
    $result = $this->html->info($args);
    $result .= $this->html->editform($args);
    return $result;
  }
  
  private function reply($id) {
    $comment = new tcomment($id);
    ttheme::$vars['comment'] = $comment;
    $args = targs::instance();
    $args->adminurl =$this->adminurl . "=$id&action";
    $result = $this->html->info($args);
    $result .= $this->html->replyform();
    return $result;
  }
  
  private function getlist($kind) {
    $result = '';
    $comments = tcomments::instance(0);
    $perpage = 20;
    // get total count
    $status = $kind == 'hold' ? 'hold' : 'approved';
    $total = $comments->db->getcount("status = '$status'");
    $from = $this->getfrom($perpage, $total);
    $list = $comments->select("$comments->thistable.status = '$status'", "order by $comments->thistable.posted desc limit $from, $perpage");
    $html = $this->html;
    $result .= sprintf($html->h2->listhead, $from, $from + count($list), $total);
    $table = $this->createtable();
    
    $args = targs::instance();
    $args->adminurl = $this->adminurl;
    $comment = new tcomment(null);
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
    
    $theme = ttheme::instance();
    $result .= $theme->getpages($this->url, litepublisher::$urlmap->page, ceil($total/$perpage));
    return $result;
  }
  
  private function getpingbackslist() {
    $result = '';
    $pingbacks = tpingbacks::instance();
    $perpage = 20;
    $total = $pingbacks->getcount();
    $from = $this->getfrom($perpage, $total);
    $items = $pingbacks->db->getitems("status <> 'deleted' order by posted desc limit $from, $perpage");
    $html = $this->html;
    $result .= sprintf($html->h2->pingbackhead, $from, $from + count($items), $total);
    $result .= $html->pingbackheader();
    $args = targs::instance();
    $args->adminurl = $this->adminurl;
    foreach ($items as $item) {
      $args->add($item);
      $args->idpost = $item['post'];
      unset($args->data['$post']);
      $args->website = sprintf('<a href="%1$s">%1$s</a>', $item['url']);
      $args->localstatus = tlocal::$data['commentstatus'][$item['status']];
      $args->date = tlocal::date(strtotime($item['posted']));
      $post = tpost::instance($item['post']);
      ttheme::$vars['post'] = $post;
      $args->posttitle =$post->title;
      $args->postlink = $post->link;
      $result .=$html->pingbackitem($args);
    }
    $result .= $html->tablefooter();
    $result = $html->fixquote($result);
    
    $theme = ttheme::instance();
    $result .= $theme->getpages($this->url, litepublisher::$urlmap->page, ceil($total/$perpage));
    return $result;
  }
  
  private function editpingback($id) {
    $pingbacks = tpingbacks::instance();
    $args = targs::instance();
    $args->add($pingbacks->getitem($id));
    return $this->html->pingbackedit($args);
  }
  
  private function moderated($id) {
    $result = $this->html->h2->successmoderated;
    $result .= $this->getinfo($id);
    return $result;
  }
  
  private function getinfo($id) {
    if (!isset(ttheme::$vars['comment'])) ttheme::$vars['comment'] = new tcomment($id);
    $args = targs::instance();
    $args->adminurl =$this->adminurl . "=$id&action";
    return $this->html->info($args);
  }
  
  private function confirmdelete($id) {
    $result = $this->getconfirmform($id, $this->lang->confirmdelete);
    $result .= $this->getinfo($id);
    return $result;
  }
  
  private function getconfirmform($id, $confirm) {
    $args = targs::instance();
    $args->id = $id;
    $args->action = 'delete';
    $args->adminurl = litepublisher::$site->url . $this->url . litepublisher::$site->q . 'id';
    $args->confirm = $confirm;
    return $this->html->confirmform($args);
  }
  
  private function deleteauthor($uid) {
    $comusers = tcomusers::instance();
    if (!$comusers->itemexists($uid)) return false;
    $comments = tcomments::instance();
    $comments->db->delete("author = $uid");
    $comusers->delete($uid);
    return true;
  }
  
  private function editauthor($id) {
    $args = targs::instance();
    if ($id == 0) {
      $args->id = 0;
      $args->name = '';
      $args->email = '';
      $args->url = '';
      $args->subscribed = '';
    } else {
      $comusers = tcomusers::instance();
      if (!$comusers->itemexists($id)) return $this->notfound;
      $args->add($comusers->getitem($id));
      $args->subscribed = $this->getsubscribed($id);
    }
    return $this->html->authorform($args);
  }
  
  private function getauthorslist() {
    $comusers = tcomusers::instance();
    $args = targs::instance();
    $perpage = 20;
    $total = $comusers->count;
    $from = $this->getfrom($perpage, $total);
    $res = $comusers->db->query("select * from $comusers->thistable limit $from, $perpage");
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
    
    $theme = ttheme::instance();
    $result .= $theme->getpages($this->url, litepublisher::$urlmap->page, ceil($total/$perpage));
    return $result;
  }
  
  private function getsubscribed($authorid) {
    $db = litepublisher::$db;
    $authorid = (int) $authorid;
    $comusers = tcomusers::instance();
    if (!$comusers->itemexists($authorid))  return '';
    $html = $this->gethtml('moderator');
    $result = '';
    $res = $db->query("select $db->posts.id as id, $db->posts.title as title, $db->urlmap.url as url
    from $db->posts, $db->urlmap
    where $db->posts.id in (select DISTINCT $db->comments.post from $db->comments where author = $authorid)
    and $db->urlmap.id = $db->posts.idurl
    order by $db->posts.posted desc");
    $items = $db->res2assoc($res);
    
    $subscribers = tsubscribers::instance();
    $subscribed = $subscribers->getposts($authorid);
    $args = targs::instance();
    foreach ($items as $item) {
      $args->add($item);
      $args->subscribed = in_array($item['id'], $subscribed);
      $result .= $html->subscribeitem($args);
    }
    
    return $html->fixquote($result);
  }
  
  public function processform() {
$result = '';
    switch ($this->name) {
      case 'comments':
      case 'hold':
      
      if (isset($_REQUEST['action'])) {
        switch ($_REQUEST['action']) {
          case 'reply':
          $comments = tcomments::instance();
          $item = $comments->getitem($this->idget() );
          $post = tpost::instance( (int) $item['post']);
          $this->manager->reply($this->idget(), $post->id, $_POST['content']);
          return turlmap::redir301($post->lastcommenturl);
          
          case 'edit':
          $comments = tcomments::instance();
          $comments->edit($this->idget(), 0, $_POST['content']);
          break;
        }
      } else {
        $manager = $this->manager;
        $comments = tcomments::instance(0);
        $status = isset($_POST['approve']) ? 'approved' : (isset($_POST['hold']) ? 'hold' : 'delete');
        foreach ($_POST as $key => $id) {
          if (!is_numeric($id))  continue;
          if (!strbegin($key, 'checkbox-item-')) continue;
          $id = (int) $id;
          if ($idpost = $comments->getvalue($id, 'post')) {
            if ($status == 'delete') {
              $manager->delete($id, $idpost);
            } else {
              $manager->setstatus($id, $idpost, $status);
            }
          }
        }
      }
      $result = $this->html->h2->successmoderated;
      break;
      
      case 'pingback':
      $pingbacks = tpingbacks::instance();
      if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit') {
        extract($_POST, EXTR_SKIP);
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
        
        $result =  $this->html->h2->authoredited;
      }
      break;
      
      case 'holdrss':
      extract($_POST, EXTR_SKIP);
      $rss = trssholdcomments::instance();
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
  
}//class
?>