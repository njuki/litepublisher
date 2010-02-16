<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/


class tadminpostcontentplugin {
  
  public function getcontent() {
    $plugin = tpostcontentplugin ::instance();
    $tml = file_get_contents(dirname(__file__) . DIRECTORY_SEPARATOR . "postcontent" . litepublisher::$options->language . ".tml");
    $html = THtmlResource::instance();
    $args = targs::instance();
    $args->before = $plugin->before;
    $args->after = $plugin->after;
    return $html->parsearg($tml, $args);
  }
  
  public function processform() {
    extract($_POST);
    $plugin = tpostcontentplugin ::instance();
    $plugin->lock();
    $plugin->before = $before;
    $plugin->after = $after;
    $plugin->unlock();
    return '';
  }
  
}
?>