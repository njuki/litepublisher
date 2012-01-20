<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tperm extends titem {

  public static function i($id = 0) {
$perms = tperms::i();
$class = $perms->itemexists($id) ? $perms->items[$id]['class'] : __class__;
    return parent::iteminstance($class, $id);
  }
  
  public static function getinstancename() {
    return 'perm';
  }
  
  protected function create() {
    parent::create();
    $this->data = array(
    'id' => 0,
'class' => __class__,
    'name' => 'default'
    );
  }
  
  public function load() {
    $perms = tperms::i();
    if ($perms->itemexists($this->id)) {
      $this->data = &$perms->items[$this->id];
      return true;
    }
    return false;
  }
  
  public function save() {
    return tperms::i()->save();
  }

public function getheader($obj) {
}  

}//class

class tperms extends titems_storage {
public $tables;

  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = false;
    parent::create();
    $this->basename = 'perms';
$this->tables = array('posts', 'tags', 'categories');
  }
  
  public function add(tperm $perm) {
    $this->lock();
    $id = ++$this->autoid;
    $perm->id = $id;
    $this->items[$id] = &$perm->data;
    $this->unlock();
    return $id;
  }
  
  public function delete($id) {
if (!isset($this->items[$id])) return false;
if (dbversion) {
$db = litepublisher::$db;
foreach ($this->tables as $table) {
$db->table = $table;
$db->update('idperm = 0', "where idperm = $id");
}
}
    return parent::delete($id);
  }

}//class