<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

define('litepublisher_mode', 'mobile');
require('../index.php');

class tmobilesite extends tsite {

  public static function instance() {
    return getinstance(__class__);
  }

  public function geturl() {
return parent::geturl() . '/mobile';
  }
  
public function install() {}
public function uninstall() {}
}//class

class tmobiletemplate extends ttemplate {

  public static function instance() {
    return getinstance(__class__);
  }

  public function request($context) {
    $this->context = $context;
    ttheme::$vars['context'] = $context;
    ttheme::$vars['template'] = $this;
    $this->itemplate = $context instanceof itemplate;
    $this->view = $this->itemplate ? tview::getview($context) : tview::instance();
    $theme = ttheme::getinstance('pda');
    litepublisher::$classes->instances[get_class($theme)] = $theme;
    $this->path = litepublisher::$paths->themes . $theme->name . DIRECTORY_SEPARATOR ;
    $this->url = litepublisher::$site->files . '/themes/' . $theme->name;
    $this->hover = $this->hovermenu && ($theme->templates['menu.hover'] == 'true');
    $this->ltoptions['themename'] = $theme->name;
    $result = $this->httpheader();
    $result  .= $theme->gethtml($context);
    unset(ttheme::$vars['context'], ttheme::$vars['template']);
    return $result;
  }
  
}//class

litepublisher::$site = tmobilesite::instance();
litepublisher::$paths->cache = litepublisher::$paths->cache . 'mobile.';
litepublisher::$classes->instances['ttemplate'] = tmobiletemplate::instance();

$url = $_SERVER['REQUEST_URI'];
    if (strbegin($url, '/mobile/') || ($url == '/mobile')) {
      if ($url == '/mobile') {
        $url = '/';
      } else {
        $url = substr($url, strlen('/mobile'));
      }
}

try {
    litepublisher::$urlmap->request(strtolower($_SERVER['HTTP_HOST']), $url);
} catch (Exception $e) {
  litepublisher::$options->handexception($e);
}
litepublisher::$options->savemodified();
litepublisher::$options->showerrors();
