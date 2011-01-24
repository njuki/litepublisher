<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminmenulinks {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function getcontent() {
    $plugin = tmenulinks::instance();
    $about = tplugins::getabout(tplugins::getname(__file__));
    $args = targs::instance();
    $args->before= $this->getitems($plugin->before);
    $args->after = $this->getitems($plugin->after);
    $args->formtitle = $about['formtitle'];
    $args->data['$lang.before'] = $about['before'];
    $args->data['$lang.after'] = $about['after'];
    
    $html = tadminhtml::instance();
    return $html->adminform('[editor=before] [editor=after]', $args);
  }
  
  public function processform() {
    $plugin = tmenulinks::instance();
    $plugin->before = $this->setitems($_POST['before']);
    $plugin->after = $this->setitems($_POST['after']);
    $plugin->save();
  }
  
  private function getitems(array &$items) {
    $result = '';
    foreach ($items as $item) {
      $result .= sprintf("[%s] = %s\n", $item['url'], $item['title']);
    }
    return $result;
  }
  
  private function setitems($s) {
    $result = array();
$siteurl = litepublisher::$site->url;
    $s = trim($s);
    $ini = tini2array::parsesection($s);
    foreach ($ini as $k => $v) {
$item = array('title' => $v);
      $k = trim($k, '[] ');
if (strbegin($k, $siteurl)) {
$item['url'] = substr($k, strlen($siteurl));
} elseif (strbegin($k, 'http://')) {
$item['link'] = $k;
} else {
$item['url'] = $k;
}
      $result[] = $item;
    }
    return $result;
  }
  
}//class
?>