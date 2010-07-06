<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tbackup2dropbox extends tplugin {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->data['idcron'] = 0;
$this->data['email'] = '';
$this->data['password'] = '';
$this->data['dir'] = '';
  }
  
  public function send() {
if ($this->password == '') return;
    $backuper = tbackuper::instance();
    $filename  = $backuper->createbackup();
    
require_once(dirname(__file__) . DIRECTORY_SEPARATOR . 'DropboxUploader.php');

        $uploader = new DropboxUploader($this->email, $this->password);
try {
        $uploader->upload($filename, $this->dir);
unlink($filename);
    } catch (Exception $e) {
return $e->getMessage();
}
return true;
   }
  
}//class

?>