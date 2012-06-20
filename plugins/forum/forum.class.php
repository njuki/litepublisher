<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tforum extends tplugin {
public $cats;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function create() {
    parent::create();
$this->basename = 'forum';
$this->data['idview'] = 1;
$this->addmap('cats', array());
  }
  
} //class