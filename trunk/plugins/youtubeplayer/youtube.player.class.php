<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tyoutubeplayer extends tplugin {

  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
$this->data['template'] ='<object width="425" height="350">
  <param name="movie" value="$url"></param>
  <param name="wmode" value="transparent"></param>
  <embed src="$url" 
    type="application/x-shockwave-flash" wmode="transparent" 
    width="425" height="350">
  </embed>
</object>';
  }
  
  public function filter(&$content) {
if (preg_match_all("/\[youtube\=http:\/\/([a-zA-Z0-9\-\_]+\.|)youtube\.com\/watch(\?v\=|\/v\/|#!v=)([a-zA-Z0-9\-\_]{11})([^<\s]*)\]/",  $content, $m, PREG_SET_ORDER)) {
      foreach ($m as $item) {
$url = 
$content = str_replace($item[0], 
str_replace('$url', $url, $this->template), $content);
}
}
  }
  
  public function install() {
    $filter = tcontentfilter::instance();
    $filter->afterfilter = $this->filter;
  }
  
  public function uninstall() {
    $filter = tcontentfilter::instance();
    $filter->unsubscribeclass($this);
  }
  
}//class
?>