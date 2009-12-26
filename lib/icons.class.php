<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class ticons extends titems {

  public static function instance() {
    return getinstance(__class__);
  }
  
public function getid($name) {
return isset($this->items[$name]) ? $this->items[$name] : 0;
}

public function geturl($name) {
if (isset($this->items[$name])) {
$files = tfiles::instance();
return $files->geturl($this->items[$name]);
}
return '';
}

public function geticon($name) {
if (isset($this->items[$name])) {
$files = tfiles::instance();
return $files->geticon($this->items[$name]);
}
return '';
}

public function filedeleted($idfile) {
foreach ($this->items as $name => $id) {
if ($id == $idfile) {
$this->delete($name);
return true;
}
}
}

}//class
?>