<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tredirector extends titems {
  public static function instance() {
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
    if (strbegin($url, litepublisher::$options->url)) return substr($url, strlen(litepublisher::$options->url));
    
    //fix for 2.xx versions
    if (preg_match('/^\/comments\/(\d*?)\/?$/', $url, $m)) return sprintf('/comments/%d.xml', $m[1]);
    if (preg_match('/^\/authors\/(\d*?)\/?$/', $url, $m)) return '/comusers.htm?id=' . $m[1];
    
    if (strpos($url, '%')) {
      $url = rawurldecode($url);
      if (strbegin($url, litepublisher::$options->url)) return substr($url, strlen(litepublisher::$options->url));
      if (litepublisher::$urlmap->urlexists($url)) return turlmap::redir301($url);
    }
    return false;
  }
  
}//class
?>