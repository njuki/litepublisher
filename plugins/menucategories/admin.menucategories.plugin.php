<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadmincategoriesmenu  {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function getcontent() {
    $plugin = tcategoriesmenu::instance();
    $about = tplugins::getabout(tplugins::getname(__file__));
    $args = targs::instance();
      $args->cats = tposteditor::getcategories($plugin->exitems);
    $args->formtitle = $about['formtitle'];
//    $args->data['$lang.before'] = $about['before'];
    
    $html = tadminhtml::instance();
    return $html->adminform('$cats', $args);
  }
  
  public function processform() {
    $plugin = tcategoriesmenu::instance();
      $plugin->exitems = tadminhtml::check2array('category-');
$plugin->save();
  }
  
}//class
