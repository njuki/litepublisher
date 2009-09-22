<?php

class TImporter extends TPlugin {
  
  public static function &Instance() {
    return GetInstance(__class__);
  }
  
  protected function CreateData() {
    parent::CreateData();
  }
  
  public function import($s) {
  }
  
  //template
  public function Getcontent() {
    $html = THtmlResource::instance();
    $html->section = 'importer';
    return $html->form();
  }
  
  public function ProcessForm() {
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
    
    if ($test) TDataClass::$GlobalLock = true;
    $this->import($s);
    
    $posts = &TPosts::Instance();
    $items = array_slice(array_keys($posts->items), -5, 5);
    
    
    $TemplatePost = &TTemplatePost::Instance();
    return  $TemplatePost->PrintPosts($items);
  }
  
}//class
?>