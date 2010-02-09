<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tmobileclasses extends tclasses {
  
  protected function create() {
    global $paths;
    $paths['cache'] .= 'pda.';
    parent::create();
    $this->remap = array(
    'tclasses' => __class__,
    'toptions' => 'tmobileoptions',
    'turlmap' => 'tmobileurlmap',
    'ttemplate' => 'tmobiletemplate',
    'TTemplateComment' => 'TMobileTemplateComment'
    );
  }
  
}//class

class tmobileoptions  extends toptions {
  public function geturl() {
    $result = parent::geturl();
    return $result . '/pda';
  }
}//class

class tmobiletemplate extends ttemplate {
  
  protected function create() {
    parent::create();
    $this->basename = 'template.pda' ;
  }
  
}//class

class tmobileurlmap extends turlmap {
  protected function prepareurl($host, $url) {
    parent::prepareurl($host, $url);
    if ($this->mobile = strbegin($this->url, '/pda/') || ($this->url == '/pda')) {
      if ($this->url == '/pda') {
        $this->url = '/';
      } else {
        $this->url = substr($this->url, strlen('/pda'));
      }
    }
    
  }
}

?>