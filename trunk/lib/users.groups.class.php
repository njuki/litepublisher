<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tusergroups extends titems {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'usergroups';
    $this->data['defaultgroup'] = 'nobody';
  }
  
  function add($name) {
    $this->items[++$this->autoid] = array(
    'name' => $name
    );
    $this->save();
    return $this->autoid;
  }
  
  public function groupid($name) {
    foreach ($this->items as $id => $item) {
      if ($name == $item['name']) return $id;
    }
    return false;
  }
  
  public function hasright($who, $group) {
    if ($who == $group) return  true;
    if (($who == 'admin') || ($group == 'nobody')) return true;
    switch ($who) {
      case 'editor':
      return $group == 'author';
      
      case 'moderator':
      return ($group == 'subscriber') || ($group == 'author');
    }
    return false;
  }
  
}//class
?>