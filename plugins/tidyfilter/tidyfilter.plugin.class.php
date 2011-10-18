<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class ttidyfilter extends tplugin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function gethtml($s) {
    return sprintf('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' .
    '<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
    <title>title</title>
    </head>
    <body>%s</body></html>', $s);
  }
  
  public function getbody($s) {
    $i = strpos($s, '<body>') + 6;
    $j = strpos($s, '</body');
    return substr($s, $i, $j - $i);
  }
  
  public function filter(&$content) {
    $config = array(
    'indent'         => true,
    'output-xhtml'   => true,
    'wrap'           => 200);
    
    $tidy = new tidy;
    $tidy->parseString($this->gethtml($content), $config, 'utf8');
    $tidy->cleanRepair();
    $content = $this->getbody((string) $tidy);
  }
  
  public function install() {
    if (!class_exists('tidy')) die('PHP tidy extension is required');
    $filter = tcontentfilter::i();
    $filter->lock();
    $filter->onaftersimple = $this->filter;
    $filter->onaftercomment = $this->filter;
    $filter->unlock();
  }
  
  public function uninstall() {
    $filter = tcontentfilter::i();
    $filter->unbind($this);
  }
  
}//class