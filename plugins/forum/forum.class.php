<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tforum extends tplugin {

  public static function i() {
    return getinstance(__class__);
  }
  
  public function create() {
    parent::create();
$this->basename = 'forum';
$this->data['idview'] = 1;
$this->data['rootcat'] = 0;
  }

public function getcombocats() {
$result = '';
$cats = tcategories::i();
$cats->loadall();
$items = $cats->getchilds($this->rootcat);

return $result;
}
  
} //class