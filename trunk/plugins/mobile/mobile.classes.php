<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tmobileoptions  extends toptions {
  public function geturl() {
    $result = parent::geturl();
    return $result . '/mobile';
  }

public function install() {}
public function uninstall() {}
}//class

class tmobiletemplate extends ttemplate {
  
  protected function create() {
    parent::create();
    $this->basename = 'template.mobile' ;
  }
  
}//class

class tmobileurlmap extends turlmap {
  protected function prepareurl($host, $url) {
    parent::prepareurl($host, $url);
    if ($this->mobile = strbegin($this->url, '/mobile/') || ($this->url == '/mobile')) {
      if ($this->url == '/mobile') {
        $this->url = '/';
      } else {
        $this->url = substr($this->url, strlen('/mobile'));
      }
    }
    
  }

public function install() {}
public function uninstall() {}

}//class

?>