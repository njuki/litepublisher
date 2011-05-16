<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadmincustomtitle {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function getcontent() {
    $plugin = tcustomtitle::instance();
    $about = tplugins::getabout(tplugins::getname(__file__));
    $args = targs::instance();
    $args->post = $plugin->post;
    $args->tag = $plugin->tag;
    $args->home = $plugin->home;
    $args->archive = $plugin->archive;
    $args->formtitle = $about['formtitle'];
    $args->data['$lang.tag'] = $about['tagcat'];
    
    $html = tadminhtml::instance();
    return $html->adminform('[text=post]
    [text=tag]
    [text=home]
    [text=archive]', $args);
  }
  
  public function processform() {
    $plugin = tcustomtitle::instance();
    $plugin->post = $_POST['post'];
    $plugin->tag = $_POST['tag'];
    $plugin->home = $_POST['home'];
    $plugin->archive = $_POST['archive'];
    $plugin->save();
    litepublisher::$urlmap->clearcache();
  }
  
}//class