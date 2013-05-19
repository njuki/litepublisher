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
      $args = new targs();
      $args->id = $id;
      $args->adminurl = $this->adminurl;
      $args->action = $action;
      $args->confirm = sprintf($this->lang->confirm, $this->lang->$action, "<a href='$post->link'>$post->title</a>");
      return $this->html->confirmform($args);
    }
    
$html = $this->html;
    switch ($_GET['action']) {
      case 'delete' :
      $posts->delete($id);
      return $html->h4->confirmeddelete;
      
      case 'setdraft':
      $post->status = 'draft';
      $posts->edit($post);
      return $html->h4->confirmedsetdraft;
      
      case 'publish':
      $post->status = 'published';
      $posts->edit($post);
      return $html->h4->confirmedpublish;
    }
    
  }
  
  private function getlist() {
    $posts = tposts::i();
    $perpage = 20;
    $where = "status <> 'deleted' ";
    if ($this->isauthor) $where .= ' and author = ' . litepublisher::$options->user;
    $count = $posts->db->getcount($where);
    $from = $this->getfrom($perpage, $count);
    $items = $posts->select($where, " order by posted desc limit $from, $perpage");
    if (!$items) $items = array();
    
    $html = $this->html;
$lang = tlocal::admin('posts');
    $result =$html->getitemscount($from, $from + count($items), $count);
    $result .= $html->tableposts($items, array(
    array('center', $lang->date, '$post.date'),
    array('left', $lang->posttitle, '$post.bookmark'),
    array('left', $lang->category, '$post.category'),
    array('left', $lang->status, '$poststatus.status'),
    array('center', $lang->edit, '<a href="' . tadminhtml::getadminlink('/admin/posts/editor/', 'id') . '=$post.id">' . $lang->edit . '</a>'),
    array('center', $lang->delete, '<a href="' . $this->adminurl . '=$post.id&action=delete">' . $lang->delete . '</a>'),
    ));

    $result .= $html->formbuttons();
$result = str_replace('$form',$result, $html->simpleform);
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