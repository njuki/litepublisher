<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminpolls {

  public static function instance() {
    return getinstance(__class__);
  }
  
  public function getcontent() {
    $plugin = tpolls::instance();
    $html = tadminhtml::instance();
    $args = targs::instance();
    $about = tplugins::localabout(dirname(__file__));
    foreach ($about as $name => $value) {
      $name = 'lang' . $name;
      $args->$name = $value;
    }
    
    $args->deftitle = $plugin->deftitle;
    $args->defitems = $plugin->defitems;
    $args->deftype = $plugin->deftitle;
    $args->voted = $plugin->voted;
    foreach ($plugin->types as $name) {
      $item = $name . 'item';
      $items = $name . 'items';
      $args->$item = $plugin->templateitems[$name];
      $args->$items = $plugin->templates[$name];
    }

        $args->formtitle = $about['formtitle'];
    return $html->adminform($tml, $args);
  }
  
  public function processform() {
    extract($_POST);
    $plugin = tpolls::instance();
    $plugin->lock();
    $plugin->title = $title;
    $plugin->voted = $voted;
    
    foreach ($plugin->types as $name) {
      $item = $name . 'item';
      $items = $name . 'items';
      $plugin->templateitems[$name] = $$item;
      $plugin->templates[$name] = $$items;
    }
    
    $plugin->unlock();
    return '';
  }
  
}
?>