<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminstaticpages extends tadminmenu {
  
  public static function instance($id = 0) {
    return parent::iteminstance(__class__, $id);
  }

private function editform(targs  $args) {
$args->text = $args->rawcontent;
$args->formtitle = $this->title;
return $this->html->adminform('[text=title] [text=description] [text=keywords] [editor=text] [hidden=id]', $args);
}
  
  public function getcontent() {
    $result = '';
    $pages = tstaticpages::instance();
    $this->basename = 'staticpages';
    $html = $this->html;
    $id = $this->idget();
    if (!$pages->itemexists($id)) $id = 0;
    $args = targs::instance();
    $args->id = $id;
    $args->adminurl = $this->adminurl;
    
    if ($id > 0) {
      $item = $pages->getitem($id);
      $args->add($item);
      if (isset($_GET['action']) &&($_GET['action'] == 'delete'))  {
        if  ($this->confirmed) {
          $pages->delete($id);
          $result .= $html->h2->successdeleted;
        } else {
          $result .= $html->confirmdelete($args);
        }
      } else {
      $result .= $this->editform($args);
      }
    } else {
      $args->title = '';
      $args->description = '';
      $args->keywords = '';
      $args->rawcontent = '';
      $result .= $this->editform($args);
    }
    
    //table
    $result .= $html->listhead();
    foreach ($pages->items as $id => $item) {
      $args->add($item);
      $args->id = $id;
      $result .= $html->itemlist($args);
    }
    $result .= $html->listfooter;
    return $html->fixquote($result);
  }
  
  public function processform() {
    if (empty($_POST['title'])) return '';
    extract($_POST);
    $pages = tstaticpages::instance();
    $id = $this->idget();
    if ($id == 0) {
      $_POST['id'] = $pages->add($title, $description, $keywords, $text);
    } else {
      $pages->edit($id, $title, $description, $keywords, $text);
    }
    $this->basename = 'staticpages';
    return $this->html->h2->success;
  }
  
}//class


?>