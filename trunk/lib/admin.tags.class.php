<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadmintags extends tadminmenu {
  
  public static function instance($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function getcontent() {
    $result = '';
    $istags = $this->name == 'tags';
    $tags = $istags  ? litepublisher::$classes->tags : litepublisher::$classes->categories;
    $this->basename = 'tags';
    $html = $this->html;
    $h2 = $html->h2;
$lang = tlocal::instance('tags');
    $id = $this->idget();
    $args = targs::instance();
    $args->id = $id;
    $args->adminurl = $this->adminurl;
$args->ajax = tadminhtml::getadminlink('/admin/ajaxtageditor.htm', sprintf('id=%d&type=%s&get=', $id, $istags  ? 'tags' : 'categories'));

    if ($id ==  0) {
      $args->title = '';
      $result .= $istags ? $h2->addtag : $h2->addcategory;
      $result .= $html->form($args);
    } elseif (!$tags->itemexists($id)) {
      return $this->notfound;
    } else {
      $item = $tags->getitem($id);
      $args->add($item);
      
      if (isset($_GET['action']) &&($_GET['action'] == 'delete'))  {
        if  ($this->confirmed) {
          $tags->delete($id);
          return $h2->successdeleted;
        } else {
          return $html->confirmdelete($args);
        }
      }
      
      $result .= $istags ? $h2->edittag : $h2->editcategory;
      if (isset($_GET['full'])) {
        $args->add($tags->contents->getitem($id));
        $args->iconlink = $tags->geticonlink($id);
        $result .= $html->fullform($args);
      } else {
        $result = $html->form($args);
      }
    }
    
    //table
    $perpage = 20;
    $count = $tags->count;
    $from = $this->getfrom($perpage, $count);
    
    if (dbversion) {
$items = array();
      if ($iditems = $tags->select('', " order by parent, id asc limit $from, $perpage")) {
$items = $tags->items;
}
    } else {
      $items = array_slice($tags->items, $from, $perpage);
    }
    
$result .= $html->buildtable($items, array(
array('right', $lang->count2, '$itemscount'),
array('left', $lang->title,'<a href="$link" title="$title">$title</a>'),
array('center', $lang->edit, '<a href="$adminurl=$id">$lang.edit</a>'),
array('center', $lang->delete, '<a href="$adminurl=$id&action=delete">$lang.delete</a>')
));
    $result = $html->fixquote($result);
    
    $theme = ttheme::instance();
    $result .= $theme->getpages($this->url, litepublisher::$urlmap->page, ceil($count/$perpage));
    return $result;
  }
  
  public function processform() {
    if (empty($_POST['title'])) return '';
    extract($_POST);
    $istags = $this->name == 'tags';
    $tags = $istags  ? litepublisher::$classes->tags : litepublisher::$classes->categories;
    $id = $this->idget();
    if ($id == 0) {
      $id = $tags->add($title);
    } elseif (isset($_GET['full'])) {
      $item = $tags->getitem($id);
      $icon = isset($icon) ? $icon : $item['icon'];
      $tags->edit($id, $title, $url, $icon);
      $tags->contents->edit($id, $rawcontent, $description, $keywords);
      if (isset($theme)) $tags->contents->setvalue($id, 'theme', $theme);
    } else {
      $item = $tags->getitem($id);
      $tags->edit($id, $title, $item['url'], $item['icon']);
    }
    
    return sprintf($this->html->h2->success, $title);
  }
  
}//class


?>