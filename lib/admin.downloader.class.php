<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadmindownloader extends tadminmenu {

  
  public static function instance($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function getcontent() {
$args = targs::instance();
$args->url = tadminhtml::getparam('url', '');
$lang = tlocal::instance('downloader');
$args->formtitle =$lang->download ;
    return $this->html->adminform('[text=url]', $args);
  }
  
  public function processform() {
$url = trim($_POST['url']);
if (empty($url)) return '';
if ($s = http::get($url)) {
$backuper = tbackuper::instance();
if ($backuper->uploaditem($s, $backuper->getarchtype($url), $itemtype)) {
return 'uploaded';
}
}
}

}//class
