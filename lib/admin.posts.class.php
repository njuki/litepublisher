<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminposts extends tadminmenu {
  private $isauthor;
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function canrequest() {
    $this->isauthor = false;
    if (!litepublisher::$options->hasgroup('editor')) {
      $this->isauthor =   litepublisher::$options->hasgroup('author');
    }
  }
  
  public function getcontent() {
    if (isset($_GET['action']) && in_array($_GET['action'], array('delete', 'setdraft', 'publish'))) {
      $action = $_GET['action'];
    } else {
      return $this->getlist();
    }
    
    $id = $this->idget();
    $posts= tposts::i();
    if (!$posts->itemexists($id)) return $this->notfound;
    $post = tpost::i($id);
    if ($this->isauthor && ($r = tauthor_rights::i()->changeposts($action))) return $r;
    if (!$this->confirmed) {
      $args = targs::i();
      $args->id = $id;
      $args->adminurl = $this->adminurl;
      $args->action = $action;
      $args->confirm = sprintf($this->lang->confirm, $this->lang->$action, "<a href='$post->link'>$post->title</a>");
      return $this->html->confirmform($args);
    }
    
    $h4 = $this->html->h4;
    switch ($_GET['action']) {
      case 'delete' :
      $posts->delete($id);
      return $h4->confirmeddelete;
      
      case 'setdraft':
      $post->status = 'draft';
      $posts->edit($post);
      return $h4->confirmedsetdraft;
      
      case 'publish':
      $post->status = 'published';
      $posts->edit($post);
      return $h4->confirmedpublish;
    }
    
  }
  
  private function getlist() {
    $result = '';
    $posts = tposts::i();
    $perpage = 20;
    $where = "status <> 'deleted' ";
    if ($this->isauthor) $where .= ' and author = ' . litepublisher::$options->user;
    $count = $posts->db->getcount($where);
    $from = $this->getfrom($perpage, $count);
    $items = $posts->select($where, " order by posted desc limit $from, $perpage");
    if (!$items) $items = array();
    
    $html = $this->html;
    $result .=sprintf($html->h4->count, $from, $from + count($items), $count);
    $result .= $html->listhead();
    $args = targs::i();
    $args->adminurl = $this->adminurl;
    $args->editurl = tadminhtml::getadminlink($this->url . 'editor/', 'id');
    foreach ($items  as $id ) {
      $post = tpost::i($id);
      ttheme::$vars['post'] = $post;
    $args->status = $this->lang->{$post->status};
      $result .= $html->itemlist($args);
    }
    $result .= $html->listfooter();
    $result = $html->fixquote($result);
    
    $theme = ttheme::i();
    $result .= $theme->getpages('/admin/posts/', litepublisher::$urlmap->page, ceil($count/$perpage));
    return $result;
  }
  
  public function processform() {
    $posts = tposts::i();
    $posts->lock();
    $status = isset($_POST['publish']) ? 'published' : (isset($_POST['setdraft']) ? 'draft' : 'delete');
    if ($this->isauthor && ($r = tauthor_rights::i()->changeposts($status))) return $r;
    foreach ($_POST as $key => $id) {
      if (!is_numeric($id))  continue;
      $id = (int) $id;
      if ($status == 'delete') {
        $posts->delete($id);
      } else {
        $post = tpost::i($id);
        $post->status = $status;
        $posts->edit($post);
      }
    }
    $posts->unlock();
  }
  
}//class
?>