<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadmintags extends tadminmenu {
  
  public static function instance() {
    return getinstance(__class__);
  }

  public function gethead() {
      if (isset($_GET['full'])) {
    return sprintf('<script type="text/javascript" src="%1$s/js/litepublisher/filebrowser.js"></script>', litepublisher::$options->files);
}
return '';
  }
  
    public function getcontent() {
    $result = '';
    $istags = $this->name == 'tags';
    $tags = $istags  ? litepublisher::$classes->tags : litepublisher::$classes->categories;
    $this->basename = 'categories';
    $html = $this->html;
    $h2 = $html->h2;
    $id = $this->idget();
    $args = targs::instance();
    $args->id = $id;
    $args->adminurl = $this->adminurl;
    if ($id ==  0) {
      $args->title = '';
      $result .= $istags ? $h2->addtag : $h2->addcategory;
      $result .= $html->form($args);
    } elseif (!$tags->ItemExists($id)) {
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
$args->iconlink = $tags->geticon($id);
        $result .= $html->fullform($args);
      } else {
        $result = $html->form($args);
      }
    }
    
    //table
    $result .= $html->listhead();
    $tags->loadall();
    foreach ($tags->items as $id => $item) {
      $args->add($item);
      $result .= $html->itemlist($args);
    }
    $result .= $html->listfooter;
    $result = str_replace("'", '"', $result);
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
    } else {
      $tags->edit($id, $title, $tags->geturl($id));
    }
    
    return sprintf($this->html->h2->success, $title);
  }
  
}//class


?>