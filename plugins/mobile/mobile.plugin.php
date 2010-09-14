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
    if (!file_exists(litepublisher::$paths->home . 'mobile' .DIRECTORY_SEPARATOR . 'index.php')) die("folder 'mobile' with requried files not exists. Please copy required folder from plugin.");
    
    /*
    $filename = 'mobile.classes.php';
    $dir =  basename(dirname(__file__) );
    litepublisher::$classes->lock();
    litepublisher::$classes->Add('tmobileoptions', $filename, $dir);
    litepublisher::$classes->Add('tmobiletemplate', $filename, $dir);
    litepublisher::$classes->Add('tmobileurlmap', $filename, $dir);
    litepublisher::$classes->unlock();
    */
    $menus = tmenus::instance();
    $menu = tmenu::instance();
    $menu->parent = 0;
    $menu->order = $menus->count;
    $about = tplugins::getabout(tplugins::getname(__file__));
    $menu->title = $about['menutitle'];
    $menu->url = '/mobile/';
    $menus->add($menu);
    
    $robot = trobotstxt::instance();
    $robot->AddDisallow('/mobile/');
    
    litepublisher::$urlmap->clearcache();
  }
  
  public function uninstall() {
    $menus = tmenus::instance();
    $menus->deleteurl('/mobile/');
    
    /*
    litepublisher::$classes->lock();
    litepublisher::$classes->delete('tmobileoptions');
    litepublisher::$classes->delete('tmobiletemplate');
    litepublisher::$classes->delete('tmobileurlmap');
    litepublisher::$classes->unlock();
    */
    litepublisher::$urlmap->clearcache();
  }
  
}//class
?>