<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadmincomusers extends tadminmenu {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function getcontent() {
    $result = '';
    $this->basename = 'authors';
    $users = tusers::i();
    $lang = $this->lang;
    $html = $this->html;
    
    if ('delete' == $this->action) {
      $id = $this->idget();
      if (!$users->itemexists($id)) return $this->notfound();
      if (!$this->confirmed) return $html->confirmdelete($id, $this->adminurl, $lang->confirmdelete);
      if (!$this->deleteauthor($id)) return $this->notfount;
      $result .= $html->h4->deleted;
    }
    
    $args = new targs();
    $perpage = 20;
    $total = $users->db->getcount("status = 'comuser'");
    $from = $this->getfrom($perpage, $total);
    $res = $users->db->query("select * from $users->thistable where status = 'comuser' order by id desc limit $from, $perpage");
    $items = litepublisher::$db->res2assoc($res);
    
    $result .= sprintf($html->h4->listhead, $from, $from + count($items), $total);
    $args->tablehead = $html->header();
    $args->adminurl = $this->adminurl;
    $args->editurl = tadminhtml::getadminlink('/admin/users/', 'id');
    $tablebody = '';
    foreach ($items as $id => $item) {
      $args->add($item);
      $tablebody .= $html->item($args);
    }
    
    $args->tablebody = $tablebody;
    $result .= $html->table($args);
    
    $theme = ttheme::i();
    $result .= $theme->getpages($this->url, litepublisher::$urlmap->page, ceil($total/$perpage));
    return $result;
  }
  
  private function deleteauthor($uid) {
    $users = tusers::i();
    if (!$users->itemexists($uid)) return false;
    if ('comuser' != $users->getvalue($uid, 'status')) return false;
    $comments = tcomments::i();
    $comments->db->delete("author = $uid");
    $users->setvalue($uid, 'status', 'hold');
    return true;
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
    return '';
    $result = '';
    if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit') {
      $id = $this->idget();
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
  }
  
}//class