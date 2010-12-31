<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminadminhover {
  
  public function getcontent() {
    $plugin = tadminhoverplugin::instance();
    $about = tplugins::localabout(dirname(__file__));
    $html = tadminhtml::instance();
    $args = targs::instance();
    $args->hovermenu = $plugin->hover;
    $args->data['$lang.hovermenu']= $about['langhovermenu'];
    $args->formtitle = $about['name'];
    return $html->adminform('[checkbox=hovermenu]', $args);
  }
  
  public function processform() {
    $plugin = tadminhoverplugin::instance();
    $plugin->hover = isset($_POST['hovermenu']);
    $plugin->save();
    return '';
  }
  
}
?>