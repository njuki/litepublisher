<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class tbackup2email extends tplugin {
 
 public static function instance() {
  return getinstance(__class__);
 }

 protected function create() {
  parent::create();
$this->data['idcron'] = 0;
}

 public function send() {
$backuper = tbackuper::instance();
  $s = $backuper->getpartial(false, false, false);
  $date = date('d-m-Y');
  $filename = litepublisher::$domain . ".$date.zip";

$dir = dirname(__file__) . DIRECTORY_SEPARATOR;
$ini = parse_ini_file($dir . 'about.ini');

tmailer::SendAttachmentToAdmin("[backup] $filename", $ini['body'], $filename, $s);
}

}//class

?>