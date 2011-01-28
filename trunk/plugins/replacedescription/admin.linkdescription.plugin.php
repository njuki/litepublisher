<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminlinkdescription {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function getcontent() {
    $plugin = tlinkdescription::instance();
    $about = tplugins::getabout(tplugins::getname(__file__));
    $args = targs::instance();
    $args->description = $plugin->description;
    $args->formtitle = $about['formtitle'];
    //$args->data['$lang.before'] = $about['before'];
    
    $html = tadminhtml::instance();
    return $html->adminform('[editor=description] ', $args);
  }
  
  public function processform() {
    $plugin = tlinkdescription::instance();
    $plugin->description = $_POST['description'];
    $plugin->save();
    litepublisher::$urlmap->clearcache();
  }
  
}//class