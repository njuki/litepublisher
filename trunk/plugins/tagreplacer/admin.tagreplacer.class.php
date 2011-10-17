<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadmintagreplacer {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function  gethead() {
    return tuitabs::gethead();
  }
  
  public function getcontent() {
    $plugin = ttagreplacer ::i();
    $html = tadminhtml::i();
    $tabs = new tuitabs();
    $args = targs::i();
    $about = tplugins::getabout(tplugins::getname(__file__));
    $args->formtitle = $about['formtitle'];
    $args->data['$lang.before'] = $about['before'];
    $args->data['$lang.after'] = $about['after'];
    $args->before = $plugin->before;
    $args->after = $plugin->after;

    $tabs->add($about['lite'], tposteditor::getcategories($plugin->lite));
foreach ($plugin->items as $i => $item) {
    $tabs->add($about['lite'], tposteditor::getcategories($plugin->lite));
}
    return $html->adminform($tabs->get(), $args);
  }
  
  public function processform() {
    $plugin = ttagreplacer ::i();
    $plugin->lock();
foreach ($_POST as $name => $value) }
if (!strbegin($name,
$value = trim($value);

}
    $plugin->unlock();
    return '';
  }
  
}