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

public static function getradio($idicon) {
$items = self::getallicons();
if (count($items) == 0) return '';
$html = tadminhtml::instance();
$html->section = 'files';
$args = targgs::instance();
//add empty icon
    $args->id = 0;
    $args->checked = 0 == $idicon;
    $args->filename = '';
    $args->title = tlocal::$data['common']['empty'];
    $result = $html->radioicon($args);
    $files = tfiles::instance();
    foreach ($items as $id) {
      $item = $files->getitem($id);
      $args->add($item);
      $args->id = $id;
      $args->checked = $id == $idicon;
$args->link = litepublisher::$site->files . $item['filename'];
      $result .= $html->radioicon($args);
    }
    
    return $result;
}

  public static function getallicons() {
    $files = tfiles::instance();
    if ($files->dbversion) {
      if ($result = $files->select("parent = 0 and media = 'icon'", "")) return $result;
return array();
    } else {
    $result = array();
      foreach ($files->items as $id => $item) {
        if ('icon' == $item['media']) $result[] = $id;
      }
    return $result;
    }
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
    
    $allicons = self::getallicons();
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