<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminicons extends tadminmenu {
  public static function instance($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  private function getallicons() {
    $result = array();
    $files = tfiles::instance();
    if ($files->dbversion) {
      $result = $files->select("parent = 0 and media = 'icon'", "");
      if (!$result) $result = array();
    } else {
      foreach ($files->items as $id => $item) {
        if ('icon' == $item['media']) $result[] = $id;
      }
    }
    return $result;
  }
  
  public function getcontent() {
    $result = '';
    $files = tfiles::instance();
    $icons = ticons::instance();
    $html = $this->html;
    $lang = tlocal::instance('files');
    $args = targs::instance();
    $a = array();
    //добавить 0 для отсутствия иконки
    $a[0] = $lang->noicon;
    
    $allicons = $this->getallicons();
    foreach ($allicons as $id) {
      $args->id = $id;
      $item = $files->getitem($id);
      $args->add($item);
      $a[$id] = $html->comboitem($args);
    }
    
    $result .= $html->iconheader();
    foreach ($icons->items as $name => $id) {
      $args->name = $name;
      $title = $lang->$name;
      if ($title == '') $title = tlocal::$data['names'][$name];
      $args->title = $title;
      $args->combo = $html->array2combo($a, $id);
      $result .= $html->iconitem($args);
    }
    $result .= $html->iconfooter();
    return $html->fixquote($result);
  }
  
  public function processform() {
    $icons = ticons::instance();
    foreach ($_POST as $name => $value) {
      if (isset($icons->items[$name])) $icons->items[$name] = (int) $value;
    }
    $icons->save();
    
    $lang = tlocal::instance('files');
    return $this->html->h2->iconupdated;
  }
  
}//class
?>