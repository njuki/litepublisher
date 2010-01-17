<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class TXMLRPCComments extends TXMLRPCAbstract {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function delete($login, $password, $id, $idpost) {
global $options;
$this->auth($login, $password);

$manager = tcommentmanager::instance();
return $manager->delete($id, $idpost);
}

}//class
?>