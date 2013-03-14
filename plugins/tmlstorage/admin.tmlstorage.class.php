<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class admintmlstorage implements iadmin {

  public static function i() {
    return getinstance(__class__);
  }

  public function getcontent() {
    $plugin = tmlstorage::i();
    $args = new targs();
$args->formtitle = 'Template storage';
$html = tadminhtml::i();
    $tabs = new tuitabs();
foreach ($plugin->items as $classname => $items) {
      $tab = new tuitabs();
foreach ($items as $key => $value) {
        $tab->add($key, $html->getinput('editor',
        $classname . '_text_' . $key, tadminhtml::specchars($value), $key));
}
      $tabs->add($classname, $tab->get());
}

    return tuitabs::gethead() .
$html->adminform($tabs->get(), $args);
}
  
  public function processform() {
    $plugin = tmlstorage::i();
foreach ($plugin->items as $classname => $items) {
foreach ($items as $key => $value) {
        $plugin->items[$classname][$key] = $_POST[$classname . '_text_' . $key];
}
}
   
    $plugin->save();
  }
  
}//class