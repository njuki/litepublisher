<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class titemsposts extends titems {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'itemsposts';
    $this->table = 'itemsposts';
  }
  
  public function add($idpost, $iditem) {
    if (dbversion) {
      $this->db->add(array(
      'post' => $idpost,
      'item' => $iditem
      ));
      $this->added();
    } else {
      if (!isset($this->items[$idpost]))  $this->items[$idpost] = array();
      if (!in_array($iditem, $this->items[$idpost])) {
        $this->items[$idpost][] =$iditem;
        $this->save();
        $this->added();
        return true;
      }
      return false;
    }
  }
  
  public function exists($idpost, $iditem) {
    if ($this->dbversion) {
      return $this->db->exists("post = $idpost and item = $iditem");
    } else {
      return isset($this->items[$idpost]) && is_int(array_search($iditem, $this->items[$idpost]));
    }
  }
  
  public function remove($idpost, $iditem) {
    if (dbversion) {
      return $this->db->delete("post = $idpost and item = $iditem");
    } elseif (isset($this->items[$idpost])) {
      $i = array_search($iditem, $this->items[$idpost]);
      if (is_int($i))  {
        array_splice($this->items[$idpost], $i, 1);
        $this->save();
        $this->deleted();
        return true;
      }
      return false;
    }
  }
  
  public function delete($idpost) {
    return $this->deletepost($idpost);
  }
  
  public function deletepost($idpost) {
    if (dbversion) {
      $result = litepublisher::$db->res2id(litepublisher::$db->query("select item from $this->thistable where post = $idpost"));
      $this->db->delete("post = $idpost");
      return $result;
    } else {
      if (isset($this->items[$idpost])) {
        $result = $this->items[$idpost];
        unset($this->items[$idpost]);
        $this->save();
        return $result;
      } else {
        return array();
      }
    }
  }
  
  public function deleteitem($iditem) {
    if (dbversion) {
      $this->db->delete("item = $iditem");
    } else {
      foreach ($this->items as $idpost => $item) {
        $i = array_search($iditem, $item);
        if (is_int($i))  array_splice($this->items[$idpost], $i, 1);
      }
      $this->save();
    }
    $this->deleted();
  }
  
  public function setitems($idpost, array $items) {
    $items = array_unique($items);
    // delete zero item
    if (false !== ($i = array_search(0, $items))) array_splice($items, $i, 1);
    $db = $this->db;
    $old = $this->getitems($idpost);
    $add = array_diff($items, $old);
    $delete = array_diff($old, $items);
    
    if (count($delete) > 0) {
      $db->delete("post = $idpost and item in (" . implode(', ', $delete) . ')');
    }
    
    if (count($add) > 0) {
      $vals = array();
      foreach ($add as $iditem) {
        $vals[]= "($idpost, $iditem)";
      }
      $db->exec("INSERT INTO $this->thistable (post, item) values " . implode(',', $vals) );
    }
    
    return array_merge($old, $add);
  }
  
  public function getitems($idpost) {
    return litepublisher::$db->res2id(litepublisher::$db->query("select item from $this->thistable where post = $idpost"));
  }
  
  public function getposts($iditem) {
    return litepublisher::$db->res2id(litepublisher::$db->query("select post from $this->thistable where item = $iditem"));
  }
  
  public function getpostscount($ititem) {
    $db = $this->getdb('posts');
    return $db->getcount("$db->posts.status = 'published' and id in (select post from $this->thistable where item = $ititem)");
  }
  
  public function updateposts(array $list, $propname) {
    $db = $this->db;
    foreach ($list as $idpost) {
      $items = $this->getitems($idpost);
      $db->table = 'posts';
      $db->setvalue($idpost, $propname, implode(', ', $items));
    }
  }
  
}//class

class titemspostsowner extends titemsposts {
  private $owner;
  public function __construct($owner) {
    if (!isset($owner)) return;
    parent::__construct();
    $this->owner = $owner;
    if ($owner->dbversion) {
      $this->table = $owner->table . 'items';
    } else {
      $this->items = &$owner->data['itemsposts'];
    }
    
    $this->dbversion = $owner->dbversion;
  }
  
public function load() { }
public function save() { $this->owner->save(); }
public function lock() { $this->owner->lock(); }
public function unlock() { $this->owner->unlock(); }
  
}//class