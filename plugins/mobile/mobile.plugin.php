<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tmobileplugin extends tplugin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function install() {
    if (!file_exists(litepublisher::$paths->home . 'mobile' .DIRECTORY_SEPARATOR . 'index.php')) die("folder 'mobile' with requried files not exists. Please copy required folder from plugin.");
    
    $about = tplugins::getabout(tplugins::getname(__file__));
    $views = tviews::i();
    if (!isset($views->defaults['mobile'])) {
      $views->defaults['mobile'] = $views->add($about['menutitle']);
    }
    
    $idview =  $views->defaults['mobile'];
    $view = tview::i($idview);
    if ($view->themename != 'pda') {
      $view->themename = 'pda';
      $view->disableajax = true;
      $view->save();
    }
    
    $menus = tmenus::i();
    $menus->addfake('/mobile/', $about['menutitle']);
    
    $robot = trobotstxt::i();
    $robot->AddDisallow('/mobile/');
    
    litepublisher::$urlmap->clearcache();
  }
  
  public function uninstall() {
    $menus = tmenus::i();
    $menus->deleteurl('/mobile/');
    
    litepublisher::$urlmap->clearcache();
  }
  
}//class