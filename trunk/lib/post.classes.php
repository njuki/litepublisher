<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tpostclasses extends titems {
  public $classes;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = dbversion;
    parent::create();
    $this->basename = 'postclasses';
    $this->table = 'postclasses';
    $this->addmap('classes', array());
  }
  
  public function newitem($name, &$class, $id) {
    if (($name != 'post') || ($id == 0)) return;
    if ($idclass = $this->getclassname($id)) {
      if ($idclass > 0) $class = $this->classes[$idclass];
    }
  }
  
  public function getclassname($id) {
    if (isset($this->items[$id])) return $this->items[$id];
    if ($this->dbversion) {
      if ($idclass = $this->db->getvalue($id, 'idclass')) $this->items[$id] = $idclass;
      return $idclass;
    }
    return false;
  }
  
  public function postadded($id) {
    if ($id == 0) return; //fix possible bugs
    $post = tpost::instance($id);
    $idclass = $this->addclass(get_class($post));
    $this->add($id, $idclass);
  }
  
  public function postdeleted($id) {
    $this->delete($id);
  }
  
  public function addclass($class) {
    foreach ($this->classes as $id => $classname) {
      if ($class == $classname) return $id;
    }
    $id = count($this->classes) + 1;
    $this->classes[$id] = $class;
    $this->save();
    return $id;
  }
  
  public function add($id, $idclass) {
    $this->items[$id] = $idclass;
    if ($this->dbversion) {
      $this->db->add(array(
      'id' => $id,
      'idclass' => $idclass
      ));
    } else {
      $this->save();
    }
  }
  
}//class
?>