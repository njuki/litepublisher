<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tchildpost extends tpost {
  public $childdata;
  public $childtable;

/*  
  protected function create() {
    parent::create();
    $this->childtable = 'tickets';
    $this->data['childdata'] = &$this->childdata;
    $this->childdata = array(
    'id' => 0,
    'type' => 'bug',
    );
  }
*/  

  public function __get($name) {
    if ($name == 'id') return $this->data['id'];
    if (array_key_exists($name, $this->childdata)) return $this->childdata[$name];
    return parent::__get($name);
  }
  
  public function __set($name, $value) {
    if ($name == 'id') return $this->setid($value);
    if (array_key_exists($name, $this->childdata)) {
      $this->childdata[$name] = $value;
      return true;
    }
    return parent::__set($name, $value);
  }
  
  public function __isset($name) {
    return array_key_exists($name, $this->childdata) || parent::__isset($name);
  }

public function fixdata() {
//$this->childdata['reproduced'] = $this->childdata['reproduced'] == '1';
}

    protected function LoadFromDB() {
    if (!parent::LoadFromDB())  return false;
    if ($a = $this->getdb($this->childtable)->getitem($this->id)) {
      $this->childdata = $a;
      $this->fixdata();
      return true;
    }
    return false;
  }
  
  protected function SaveToDB() {
    parent::SaveToDB();
    if ($this->childdata['closed'] == '') $this->childdata['closed'] = sqldate();
    $this->childdata['id'] = $this->id;
    $this->getdb($this->childtable)->updateassoc($this->childdata);
  }
  
  public function addtodb() {
    $id = parent::addtodb();
    $this->childdata['id'] = $id;
    $this->getdb($this->childtable)->add($this->childdata);
    return $this->id;
  }
  
}//class

class tchildposts extends tposts {
  public $childstable;

/*  
  protected function create() {
    parent::create();
    $this->childstable = 'tickets';
  }

public function newpost() {
return tchildpost::instance();
}
*/
  
  public function getchildscount($where) {
    $db = litepublisher::$db;
$childstable = $db->prefix . $this->childstable;
    if ($res = $db->query("SELECT COUNT($db->posts.id) as count FROM $db->posts, $childstable
    where $db->posts.status <> 'deleted' and $childstable.id = $db->posts.id $where")) {
      if ($r = $db->fetchassoc($res)) return $r['count'];
    }
    return 0;
  }
  
  public function transformres($res) {
    $result = array();
    $t = new tposttransform();
    while ($a = litepublisher::$db->fetchassoc($res)) {
      $child = $this->newpost();
      $t->post  = $child;
      $t->setassoc($a);
      foreach ($child->childdata as $name => $value) {
        if (isset($a[$name])) $child->childdata[$name] = $value;
      }
      $child->fixdata();
      $result[] = $child->id;
    }
    return $result;
  }
  
  public function select($where, $limit) {
    $db = litepublisher::$db;
$childstable = $db.prefix . $this->childstable;
    $res = $db->query("select $db->posts.*, $db->urlmap.url as url, $childstable.*
    from $db->posts, $db->urlmap, $childstable
    where $where and  $db->posts.id = $childstable.id and $db->urlmap.id  = $db->posts.idurl $limit");
    
    return $this->transformres($res);
  }
  
  public function postdeleted($id) {
    $db = $this->getdb($this->childstable);
    $db->delete("id = $id");
  }
  
  public function optimize() {
    $db = $this->getdb($this->childstable);
    $deleted = $db->res2id($db->query("select id from $db->prefix$this->childstable where id not in
    (select $db->posts.id from $db->posts)"));
    if (count($deleted) == 0) return;
$this->deletechilds($deleted);
$db->table = $this->childstable;
    $db->deleteitems($deleted);
  }
  
  public function install() {
if (__class__ != get_class($this)) $this->externalfunc(get_class($this), 'Install', null);
  }
  
  public function uninstall() {
if (__class__ != get_class($this)) $this->externalfunc(get_class($this), 'Uninstall', null);
  }
  
}//class
?>