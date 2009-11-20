<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class TMobileClasses extends TClasses {
const remap = array(
'TClasses' => __class__,
'TOptions' => 'TMobileOptions',
'turlmap' => 'tmobileurlmap',
'ttemplate' => 'tmobiletemplate',
'TTemplateComment' => 'TMobileTemplateComment'
);

public function getinstance($class) {
if (isset(self::remap[$class])) $class = self::remap[$class];
return parent::getinstance($class);
}

}//class

class TMobileOptions  extends TOptions {
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

class TMobileTemplateComment extends TTemplateComment {
  protected function create() {
    parent::create();
    $this->basename = 'templatecomment.pda';
}

}

class tmobileurlmap extends turlmap {

protected function prepareurl($host, $url) {
parent::prepareurl($host, $url);

    if ($this->mobile = (strncmp('/pda/', $this->url, strlen('/pda/')) == 0) || ($this->url == '/pda')) {
      if ($this->url == '/pda') {
        $this->url = '/';
      } else {
        $this->url = substr($this->url, strlen('/pda'));
      }
}
 
protected function getcachefile($id) {
global $paths;
return $paths['cache']. "pda.$id-$this->page.php";
}

}

?>