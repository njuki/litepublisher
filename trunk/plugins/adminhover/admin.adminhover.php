<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminadminhover {
  
  public function getcontent() {
    $plugin = tadminhoverplugin::instance();
    $about = tplugins::localabout(dirname(__file__));
    $tml = file_get_contents(dirname(__file__) . DIRECTORY_SEPARATOR . 'admin.adminhover.tml');
    $html = tadminhtml::instance();
    $args = targs::instance();
    $args->hovermenu = $plugin->hover;
    $args->langhovermenu = $about['langhovermenu'];
    return $html->parsearg($tml, $args);
  }
  
  public function processform() {
    $plugin = tadminhoverplugin::instance();
    $plugin->hover = isset($_POST['hovermenu']);
    $plugin->save();
    return '';
  }
  
}
?>