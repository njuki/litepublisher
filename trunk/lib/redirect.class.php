<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tredirector extends titems {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = false;
    parent::create();
    $this->basename = 'redirector';
  }
  
  public function add($from, $to) {
    $this->items[$from] = $to;
    $this->save();
    $this->added($from);
  }
  
  public function get($url) {
    if (isset($this->items[$url])) return $this->items[$url];
    if (strbegin($url, litepublisher::$site->url)) return substr($url, strlen(litepublisher::$site->url));
    
    //fix for 2.xx versions
    if (preg_match('/^\/comments\/(\d*?)\/?$/', $url, $m)) return sprintf('/comments/%d.xml', $m[1]);
    if (preg_match('/^\/authors\/(\d*?)\/?$/', $url, $m)) return '/comusers.htm?id=' . $m[1];
    
    if (strpos($url, '%')) {
      $url = rawurldecode($url);
      if (strbegin($url, litepublisher::$site->url)) return substr($url, strlen(litepublisher::$site->url));
      if (litepublisher::$urlmap->urlexists($url)) return $url;
    }
    
    //fix php warnings e.g. function.preg-split
    if (($i = strrpos($url, '/')) && strbegin(substr($url, $i), '/function.')) {
      return substr($url, 0, $i + 1);
    }
    return false;
  }
  
}//class