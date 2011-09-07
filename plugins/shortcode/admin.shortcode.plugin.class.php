<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminshortcodeplugin {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function getcontent() {
    $plugin = tshortcode::instance();
    $about = tplugins::getabout(tplugins::getname(__file__));
    $args = targs::instance();
    $args->codes = $plugin->codes;
    $args->formtitle = $about['formtitle'];
    $args->data['$lang.codes'] = $about['codes'];
    
    $html = tadminhtml::instance();
    return $html->adminform('[editor=codes]', $args);
  }
  
  public function processform() {
    $plugin = tshortcode::instance();
    $plugin->setcodes($_POST['codes']);
  }
  
}//class
