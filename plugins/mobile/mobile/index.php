<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

define('litepublisher_mode', 'mobile');
require('../index.php');

class tmobilesite extends tsite {

  public static function i() {
    return getinstance(__class__);
  }

  public function geturl() {
return parent::geturl() . '/mobile';
  }

  public function getcanonicalurl() {
return parent::geturl();
  }

public function install() {}
public function uninstall() {}
}//class

class tmobiletemplate extends ttemplate {

  public static function i() {
    return getinstance(__class__);
  }

protected function get_view($context) {
    $views = tviews::i();
    $idview = isset($views->defaults['mobile']) ? $views->defaults['mobile'] : 1;
return tview::i($idview);
}

}//class

litepublisher::$site = tmobilesite::i();
litepublisher::$paths->cache = litepublisher::$paths->cache . 'mobile.';
if (tfilestorage::$memcache) litepublisher::$urlmap->cache->prefix .= 'mobile:';
litepublisher::$classes->instances['ttemplate'] = tmobiletemplate::i();

$url = $_SERVER['REQUEST_URI'];
      if ($url == '/mobile') {
        $url = '/';
      } elseif (strbegin($url, '/mobile/')) {
        $url = substr($url, strlen('/mobile'));
      }

try {
    litepublisher::$urlmap->request(strtolower($_SERVER['HTTP_HOST']), $url);
} catch (Exception $e) {
  litepublisher::$options->handexception($e);
}
litepublisher::$options->savemodified();
litepublisher::$options->showerrors();
