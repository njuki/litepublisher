<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class tmarkdownplugin extends tplugin {
public $parser;

 public static function instance() {
  return getinstance(__class__);
 }
 
 protected function create() {
  parent::create();
require_once(dirname(__file__) . DIRECTORY_SEPARATOR . 'markdown.parser.class.php');
$this->parser = new Markdown_Parser();
 }

public function filter(&$content) {
$content = $this->parser->transform($content);
}

public function install() {
$filter = tcontentfilter::instance();
$filter->beforefilter = $this->filter;
}

public function uninstall() {
$filter = tcontentfilter::instance();
$filter->unsubscribeclass($this);
}

}//class
?>