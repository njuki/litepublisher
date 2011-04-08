<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tsmushitplugin extends tplugin {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function install() {
    
    $parser = tmediaparser::instance();
    $parser->added = $this->fileadded;
  }
  
  public function uninstall() {
    $parser = tmediaparser::instance();
    $parser->unsubscribeclass($this);
  }
  
  public function fileadded($id) {
    $files = tfiles::instance();
    $item = $files->getitem($id);
    if ('image' != $item['media']) return;
    $fileurl = $files->geturl($id);
    if ($s = http::get('http://www.smushit.com/ysmush.it/ws.php?img=' . urlencode($fileurl))) {
      $json = json_decode($s);
      if ( isset ( $json->error) ||
      (-1 === intval($json->dest_size)) ||
      !$json->dest) return;
      $div = $item['size'] - (int) $json->dest_size;
      if (($div / ($item['size'] / 100)) < 3) return;
      $dest = urldecode($json->dest);
      if (!strbegin($dest, 'http')) $dest = 'http://www.smushit.com/' . $dest;
      if ($content = http::get($dest)) {
        return $files->setcontent($id, $content);
      }
    }
  }
  
}//class