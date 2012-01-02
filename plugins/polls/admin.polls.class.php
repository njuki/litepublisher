<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminpolls {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function getcontent() {
    $plugin = tpolls::i();
    $html = tadminhtml::i();
    $args = targs::i();
    $about = tplugins::localabout(dirname(__file__));
    foreach ($about as $name => $value) {
      $args->data["\$lang.$name"] = $value;
    }
    
    $args->deftitle = $plugin->deftitle;
    $args->defitems = $plugin->defitems;
    $args->deftype = tadminhtml::array2combo(array_combine($plugin->types, $plugin->types), $plugin->deftype);
    $args->defadd = $plugin->defadd;
    $args->voted = $plugin->voted;
    $form = '[text=voted]';
    $form .= sprintf('<h4>%s</h4>', $about['defoptions']);
    $form .= '[combo=deftype] [text=deftitle] [text=defitems] [checkbox=defadd] ';
    
    $form .= sprintf('<h4>%s</h4>', $about['templateitems']);
    foreach ($plugin->types as $name) {
      $item = $name . 'item';
      $items = $name . 'items';
      $args->$item = $plugin->templateitems[$name];
      $args->$items = $plugin->templates[$name];
      $form .= "[editor=$item]\n[editor=$items]\n";
    }
    
    $args->microformat = $plugin->templates['microformat'];
    $form .= '[editor=microformat]';
    
    $args->formtitle = $about['formtitle'];
    return $html->adminform($form, $args);
  }
  
  public function processform() {
    extract($_POST);
    $plugin = tpolls::i();
    $plugin->lock();
    $plugin->deftitle = $deftitle;
    $plugin->deftype = $deftype;
    $plugin->defitems = trim($defitems);
    $plugin->voted = $voted;
    $plugin->defadd = isset($defadd);
    
    foreach ($plugin->types as $name) {
      $plugin->templateitems[$name] = $_POST[$name . 'item'];
      $plugin->templates[$name] = $_POST[$name . 'items'];
    }
    $plugin->templates['microformat'] = $_POST['microformat'];
    $plugin->unlock();
    return '';
  }
  
}