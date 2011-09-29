<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminlitecategories  {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function  gethead() {
    return tuitabs::gethead();
  }
  
  public function getcontent() {
    $plugin = tlitecategories::i();
    $about = tplugins::getabout(tplugins::getname(__file__));
    $tabs = new tuitabs();
    $html= tadminhtml::i();
    $args = targs::i();
    $tabs->add($about['lite'], tposteditor::getcategories($plugin->lite));
    $tabs->add($about['expand'], str_replace('category-', 'expand_category-',
    tposteditor::getcategories($plugin->expand)));
    $args->formtitle = $about['formtitle'];
    return $html->adminform($tabs->get(), $args);
  }
  
  public function processform()  {
    $plugin = tlitecategories::i();
    $plugin->lite = tadminhtml::check2array('category-');
    $plugin->expand = tadminhtml::check2array('expand_category-');
    $plugin->save();
  }
  
}//class