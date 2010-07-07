<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tclearcache extends tplugin {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function clearcache() {
    tfiler::delete(litepublisher::$paths->data . 'themes', false, false);
    litepublisher::$urlmap->clearcache();
  }
  
  public function install() {
    litepublisher::$urlmap->beforerequest = $this->clearcache;
  }
  
  public function uninstall() {
    litepublisher::$urlmap->unsubscribeclass($this);
  }
  
}//class
?>