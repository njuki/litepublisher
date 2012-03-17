<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminregoauth2 implements iadmin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function  gethead() {
    return tuitabs::gethead();
  }
  
  public function getcontent() {
    $plugin = tregoauth2 ::i();
    $html = tadminhtml::i();
    $tabs = new tuitabs();
    $args = targs::i();
    $about = tplugins::getabout(tplugins::getname(__file__));
    $args->formtitle = $about['name'];
    
    foreach ($plugin->items as $id => $item) {
$service = getinstance($item['class']);
      $tabs->add($service->title,
      $html->getinput('text',
      "where-$i", tadminhtml::specchars($item['where']), $about['where']) .
      $html->getinput('text',
      "search-$i", tadminhtml::specchars($item['search']), $about['search']) .
      $html->getinput('editor',
      "replace-$i", tadminhtml::specchars($item['replace']), $about['replace']) );
    }
    
    return $html->adminform($tabs->get(), $args);
  }
  
  public function processform() {
    $plugin = tregoauth2 ::i();
    $plugin->lock();
    foreach ($plugin->items as $id => $item) {
$service = getinstance($item['class']);

      if (!strbegin($name, 'where-')) continue;
      $id = substr($name, strlen('where-'));
      $where = trim($value);
      if (!isset($theme->templates[$where]) || !is_string($theme->templates[$where])) continue;
      $search = $_POST["search-$id"];
      if ($search == '') continue;
      $plugin->items[] = array(
      'where' => $where,
      'search' => $search,
      'replace' => $_POST["replace-$id"]
      );
    }
    $plugin->unlock();
    return '';
  }
  
}//class