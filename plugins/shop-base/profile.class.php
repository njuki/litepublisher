<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tshopprofile extends tevents {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'shop.profile';
$this->data += array(
'title' => '',
'company' => '',
'currency' => 'USD', //'RUR
'rootcategory' => 0,
);
  }
  
}//class