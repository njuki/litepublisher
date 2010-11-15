<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminopenid {
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function getcontent() {
    $openid = topenid::instance();
    $args = targs::instance();
    $args->confirm = $openid->confirm;
    $args->usebigmath = $openid->usebigmath;
    $args->trusted = implode("\n", $openid->trusted);
    
    $tml = '[checkbox:confirm]
    [checkbox:usebigmath]
    [editor:trusted]';
    $about = tplugins::getabout(tplugins::getname(__file__));
    $args->formtitle = $about['formtitle'];
    $args->data['$lang.confirm'] = $about['confirm'];
    $args->data['$lang.usebigmath'] = $about['usebigmath'];
    $args->data['$lang.trusted'] = $about['trusted'];
    
    $html = tadminhtml::instance();
    return $html->adminform($tml, $args);
  }
  
  public function processform() {
    extract($_POST, EXTR_SKIP);
    $openid = topenid::instance();
    $openid->confirm = isset($confirm);
    $openid->usebigmath = isset($usebigmath);
    $openid->trusted = explode("\n", trim($trusted));
    $openid->save();
  }
  
}//class
?>