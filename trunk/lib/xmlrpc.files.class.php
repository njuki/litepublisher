<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class TXMLRPCFiles extends TXMLRPCAbstract {
private $html;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function delete($login, $password, $id) {
    $this->auth($login, $password, 'editor');
$files = tfiles::instance();
    if (!$files->delete((int) $id)) return $this->xerror(404, "File not deleted");
    return true;
  }

  public function getpage($login, $password, $index) {
    $this->auth($login, $password, 'editor');
$html = THtmlResource::instance();
$html->section ='files';
$lang = tlocal::instance('files');
return $this->getfilepages((int) $index);
}

  public function getbrowser($login, $password, $idpost) {
    $this->auth($login, $password, 'editor');
$html = THtmlResource::instance();
$html->section ='files';
$lang = tlocal::instance('files');
$result = $this->getswfuploadhtml();
$result .= $this->getfilepages(1);
$result .= getpostfiles((int) $idpost);
return $result;
}

private function getswfuploadhtml() {
return $result;
}

private function getfilepages($index) {


return $result;
}

private function getpostfiles($idpost) {
$result = '';
$post = tpost::instance((int) $idpost);
foreach ($post->files as $id) {
$result .= '';
}
return $result;
} 

}//class
?>