<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tmobileoptions  extends toptions {

  public static function instance() {
    return getinstance(__class__);
  }

  public function geturl() {
return parent::geturl() . litepublisher::$mobile;
  }
  
public function install() {}
public function uninstall() {}
}//class

class tmobiletemplate extends ttemplate {

  public static function instance() {
    return getinstance(__class__);
  }

  public function getcontexttheme($context) {
return ttheme::instance(litepublisher::$mobiletheme);
}
  
}//class

class tmobileurlmap extends turlmap {

  public static function instance() {
    return getinstance(__class__);
  }

public function install() {}
public function uninstall() {}
  
  protected function prepareurl($host, $url) {
    parent::prepareurl($host, $url);
    if (strbegin($this->url, litepublisher::$mobile . '/') || ($this->url == litepublisher::$mobile)) {
      if ($this->url == litepublisher::$mobile) {
        $this->url = '/';
      } else {
        $this->url = substr($this->url, strlen(litepublisher::$mobile));
      }
    }
  }
  
}//class

?>