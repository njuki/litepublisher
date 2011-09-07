<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminyoutubeplayer {
  
  public function getcontent() {
    $plugin = tyoutubeplayer::instance();
    $about = tplugins::getabout(tplugins::getname(__file__));
    $args = targs::instance();
    $args->formtitle = $about['formtitle'];
    $args->data['$lang.template'] = $about['template'];
    $args->template = $plugin->template;
    $html = tadminhtml::instance();
    return $html->adminform('[editor:template]', $args);
  }
  
  public function processform() {
    $plugin = tyoutubeplayer::instance();
    $plugin->template = $_POST['template'];
    $plugin->save();
  }
  
}//class
