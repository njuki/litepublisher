<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tcategories extends tcommontags {
  //public  $defaultid;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->table = 'categories';
    $this->contents->table = 'catscontent';
    $this->itemsposts->table = $this->table . 'items';
    $this->basename = 'categories' ;
    $this->data['defaultid'] = 0;
  }
  
  public function setdefaultid($id) {
    if (($id != $this->defaultid) && $this->itemexists($id)) {
      $thisdata['defaultid'] = $id;
      $this->save();
    }
  }

  public function save() {
    parent::save();
    if (!$this->locked)  {
tcategorieswidget::instance()->expire();
    }
  }
  
  }//class

class tcategorieswidget extends tcommontagswidget {

  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'widget.categories';
$this->template = 'categories';
}

public function getowner() {
return tcategories::instance();
}

public function gettitle($id) {
return tlocal::$data['stdwidgetnames']['categories'];  
}

}//class
?>