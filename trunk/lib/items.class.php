<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class titems extends tevents {
  public $items;
  protected $autoid;
  public $dbversion;
  
  protected function create() {
    parent::create();
    $this->addevents('added', 'deleted');
    if ($this->dbversion) {
      $this->items = array();
    } else {
      $this->addmap('items', array());
      $this->addmap('autoid', 0);
    }
  }
  
  public function load() {
    if ($this->dbversion) {
      return tstorage::load($this);
    } else {
      return parent::load();
    }
  }
  
  public function save() {
    if ($this->dbversion) {
      return tstorage::save($this);
    } else {
      return parent::save();
    }
  }
  
  public function loadall() {
    if (!$this->dbversion)  return;
    return $this->select('', '');
  }
  
  public function loaditems(array $items) {
    if (!$this->dbversion) return;
    //��������� �� �������� ����������� �����
    $items = array_diff($items, array_keys($this->items));
    if (count($items) == 0) return;
    $list = implode(',', $items);
    $this->select("$this->thistable.id in ($list)", '');
  }
  
  public function select($where, $limit) {
    if (!$this->dbversion) $this->error('Select method must be called ffrom database version');
    if ($where != '') $where = 'where '. $where;
    return $this->res2items($this->db->query("SELECT * FROM $this->thistable $where $limit"));
  }
  
  public function res2items($res) {
    if (!$res) return array();
    $result = array();
    while ($item = litepublisher::$db->fetchassoc($res)) {
      $id = $item['id'];
      $result[] = $id;
      $this->items[$id] = $item;
    }
    return $result;
  }
  
  public function getcount() {
    if ($this->dbversion) {
      return $this->db->getcount();
    } else {
      return count($this->items);
    }
  }
  
  public function getitem($id) {
    if (isset($this->items[$id])) return $this->items[$id];
    if ($this->dbversion) {
      if ($this->select("$this->thistable.id = $id", 'limit 1')) return $this->items[$id];
    }
    return $this->error("Item $id not found in class ". get_class($this));
  }
  
  public function getvalue($id, $name) {
    if ($this->dbversion && !isset($this->items[$id])) $this->items[$id] = $this->db->getitem($id);
    return $this->items[$id][$name];
  }
  
  public function setvalue($id, $name, $value) {
    $this->items[$id][$name] = $value;
    if ($this->dbversion) {
      $this->db->setvalue($id, $name, $value);
    }
  }
  
  public function itemexists($id) {
    if (isset($this->items[$id])) return true;
    if ($this->dbversion) {
      try {
        return $this->getitem($id);
      } catch (Exception $e) {
        return false;
      }
    }
    return false;
  }
  
  public function IndexOf($name, $value) {
    if ($this->dbversion){
      return $this->db->findid("$name = ". dbquote($value));
    }
    
    foreach ($this->items as $id => $item) {
      if ($item[$name] == $value) {
        return $id;
      }
    }
    return false;
  }
  
  public function additem(array $item) {
    $id = $this->dbversion ? $this->db->add($item) : ++$this->autoid;
    $item['id'] = $id;
    $this->items[$id] = $item;
    if (!$this->dbversion) $this->save();
    $this->added($id);
    return $id;
  }
  
  public function delete($id) {
    if ($this->dbversion) $this->db->delete("id = $id");
    if (isset($this->items[$id])) {
      unset($this->items[$id]);
      if (!$this->dbversion) $this->save();
      $this->deleted($id);
      return true;
    }
    return false;
  }
  
}//class

class titems_storage extends titems {
  
  public function load() {
    return tstorage::load($this);
  }
  
  public function save() {
    return tstorage::save($this);
  }
  
}//class

class tsingleitems extends titems {
  public static $instances;
  public $id;
  
  public static function singleinstance($class, $id = 0) {
    if (!isset(self::$instances)) self::$instances = array();
    if (isset(self::$instances[$class][$id]))     return self::$instances[$class][$id];
    $self = litepublisher::$classes->newinstance($class);
    self::$instances[$class][$id] = $self;
    $self->id = $id;
    $self->load();
    return $self;
  }
  
  public function load() {
    if (!isset($this->id)) return false;
    return parent::load();
  }
  
  public function free() {
    unset(self::$instances[get_class($this)][$this->id]);
  }
  
}//class

?>