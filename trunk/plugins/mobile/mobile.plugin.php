<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tmobileplugin extends tplugin {

  public static function instance() {
    return getinstance(__class__);
  }

public function install() {
$filename = 'mobile.classes.php';
$dir =  basename(dirname(__file__) );
litepublisher::$classes->lock();
  litepublisher::$classes->Add('tmobileoptions', $filename, $dir);
  litepublisher::$classes->Add('tmobiletemplate', $filename, $dir);
  litepublisher::$classes->Add('tmobileurlmap', $filename, $dir);
litepublisher::$classes->unlock();
}

public function uninstall() {
litepublisher::$classes->lock();
  litepublisher::$classes->delete('tmobileoptions');
  litepublisher::$classes->delete('tmobiletemplate');
  litepublisher::$classes->delete('tmobileurlmap');
litepublisher::$classes->unlock();

}
  
}//class
?>