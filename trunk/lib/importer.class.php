<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class timporter extends tplugin {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function import($s) {
  }
  
  //template
  public function getcontent() {
    $html = THtmlResource::instance();
    $html->section = 'importer';
    return $html->form();
  }
  
  public function processform() {
    switch ($_POST['form']) {
      case 'upload':
      if (!is_uploaded_file($_FILES["filename"]["tmp_name"])) {
        return sprintf('Possible file attack, filename: %s', $_FILES["filename"]["name"]);
      }
      $filename = $_FILES["filename"]["tmp_name"];
      $test = isset($_POST['test']);
      break;
      
      case 'local':
      $filename = $_POST['local'];
      $test = isset($_POST['test2']);
      break;
    }
    
    if ($fh = gzopen($filename, 'r')) {
      $s = '';
      while (!gzeof($fh)) $s .= gzread ($fh, 4096);
      gzclose($fh);
    }  else {
      return 'error';
    }
    
    if ($test) tdata::$GlobalLock = true;
    $this->import($s);
    
    $posts = tposts::instance();
    $items = array_slice(array_keys($posts->items), -5, 5);
    
    $theme = ttheme::instance();
    return  $theme->getposts($items, false);
  }
  
}//class
?>