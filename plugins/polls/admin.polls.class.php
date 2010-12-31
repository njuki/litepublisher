<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminpolls {
  
  public function getcontent() {
    $plugin = tpolls::instance();
    $tml = file_get_contents(dirname(__file__) . DIRECTORY_SEPARATOR . "adminform.tml");
    $html = tadminhtml::instance();
    $args = targs::instance();
    $about = tplugins::localabout(dirname(__file__));
    foreach ($about as $name => $value) {
      $name = 'lang' . $name;
      $args->$name = $value;
    }
    
    $args->title = $plugin->title;
    $args->voted = $plugin->voted;
    foreach ($plugin->types as $name) {
      $item = $name . 'item';
      $items = $name . 'items';
      $args->$item = $plugin->templateitems[$name];
      $args->$items = $plugin->templates[$name];
    }
    
    return $html->parsearg($tml, $args);
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