<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminposts extends tadminmenu {
private $isauthor;
  
  public static function instance($id = 0) {
    return parent::iteminstance(__class__, $id);
  }

  public function request($arg) {
$result = parent::request($arg);
$this->isauthor = false;
    $groupname = litepublisher::$options->group;
    if ($groupname != 'admin') {
      $groups = tusergroups::instance();
$this->isauthor =  !$groups->hasright($groupname, 'editor') &&  $groups->hasright($groupname, 'author');
}
return $result;
}
  
  public function getcontent() {
    if (isset($_GET['action']) && in_array($_GET['action'], array('delete', 'setdraft', 'publish'))) {
      $action = $_GET['action'];
    } else {
      return $this->getlist();
    }
    
    $id = $this->idget();
    $posts= tposts::instance();
    if (!$posts->itemexists($id)) return $this->notfound;
    $post = tpost::instance($id);
 if ($this->isauthor && ($r = tauthor_rights::instance()->changeposts($action))) return $r;   
    if (!$this->confirmed) {
      $args = targs::instance();
      $args->id = $id;
      $args->adminurl = $this->adminurl;
      $args->action = $action;
      $args->confirm = sprintf($this->lang->confirm, $this->lang->$action, "<a href='$post->link'>$post->title</a>");
      return $this->html->confirmform($args);
    }
    
    $h2 = $this->html->h2;
    switch ($_GET['action']) {
      case 'delete' :
      $posts->delete($id);
      return $h2->confirmeddelete;
      
      case 'setdraft':
      $post->status = 'draft';
      $posts->edit($post);
      return $h2->confirmedsetdraft;
      
      case 'publish':
      $post->status = 'published';
      $posts->edit($post);
      return $h2->confirmedpublish;
    }
    
  }
  
  private function getlist() {
    $result = '';
    $posts = tposts::instance();
    $perpage = 20;
    if (dbversion) {
      $where = "status <> 'deleted' ";
if ($this->isauthor) $where .= ' and author = ' . litepublisher::$options->user;
      $count = $posts->db->getcount($where);
      $from = $this->getfrom($perpage, $count);
      $items = $posts->select($where, " order by posted desc limit $from, $perpage");
      if (!$items) $items = array();
    } else {
      $count = $posts->count;
      $from = $this->getfrom($perpage, $count);
      $items = array_slice($posts->items, $from, $perpage, true);
      $items = array_reverse (array_keys($items));
    }
    $html = $this->html;
    $result .=sprintf($html->h2->count, $from, $from + count($items), $count);
    $result .= $html->listhead();
    $args = targs::instance();
    $args->adminurl = $this->adminurl;
    $args->editurl = litepublisher::$site->url . $this->url . 'editor/' . litepublisher::$site->q . 'id';
    foreach ($items  as $id ) {
      $post = tpost::instance($id);
      ttheme::$vars['post'] = $post;
    $args->status = $this->lang->{$post->status};
      $result .= $html->itemlist($args);
    }
    $result .= $html->listfooter();
    $result = $html->fixquote($result);
    
    $theme = ttheme::instance();
    $result .= $theme->getpages('/admin/posts/', litepublisher::$urlmap->page, ceil($count/$perpage));
    return $result;
  }
  
  public function processform() {
    $posts = tposts::instance();
    $posts->lock();
    $status = isset($_POST['publish']) ? 'published' : (isset($_POST['setdraft']) ? 'draft' : 'delete');
 if ($this->isauthor && ($r = tauthor_rights::instance()->changeposts($status))) return $r;   
    foreach ($_POST as $key => $id) {
      if (!is_numeric($id))  continue;
      $id = (int) $id;
      if ($status == 'delete') {
        $posts->delete($id);
      } else {
        $post = tpost::instance($id);
        $post->status = $status;
        $posts->edit($post);
      }
    }
    $posts->unlock();
  }
  
}//class
?>