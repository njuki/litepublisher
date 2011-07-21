<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminusernews {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function getcontent() {
    $plugin = tusernews::instance();
    $about = tplugins::getabout(tplugins::getname(__file__));
    $args = targs::instance();
    $form = '';
    foreach (array('_changeposts', '_canupload', '_candeletefile', 'autosubscribe') as $name) {
      $args->$name = $plugin->data[$name];
      $args->data["\$lang.$name"] = $about[$name];
      $form .= "[checkbox=$name]";
    }
    
    $args->formtitle = $about['formtitle'];
    $html = tadminhtml::instance();
    return $html->adminform($form, $args);
  }
  
  public function processform() {
    $plugin = tusernews::instance();
    foreach (array('_changeposts', '_canupload', '_candeletefile', 'autosubscribe') as $name) {
      $plugin->data[$name] = isset($_POST[$name]);
    }
    $plugin->save();
  }
  
}//class