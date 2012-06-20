<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminforum {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function getcontent() {
    $plugin = tforum::i();
    $about = tplugins::getabout(tplugins::getname(__file__));
    $args = targs::i();
    $form = '';
    foreach (array('_changeposts', '_canupload', '_candeletefile', 'insertsource') as $name) {
      $args->$name = $plugin->data[$name];
      $args->data["\$lang.$name"] = $about[$name];
      $form .= "[checkbox=$name]";
    }
    
    foreach (array('sourcetml') as $name) {
      $args->$name = $plugin->data[$name];
      $args->data["\$lang.$name"] = $about[$name . 'label'];
      $form .= "[text=$name]";
    }
    
    $args->formtitle = $about['formtitle'];
    $html = tadminhtml::i();
    return $html->adminform($form, $args);
  }
  
  public function processform() {
    $plugin = tforum::i();
    foreach (array('_changeposts', '_canupload', '_candeletefile', 'insertsource') as $name) {
      $plugin->data[$name] = isset($_POST[$name]);
    }
    foreach (array('sourcetml') as $name) {
      $plugin->data[$name] = $_POST[$name];
    }
    $plugin->save();
  }
  
}//class