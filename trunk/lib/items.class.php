<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class titems extends tevents {
  public $items;
  protected $autoid;
  protected $dbversion;
  
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
    global $options;
    if ($this->dbversion) {
      if (!isset($options->data[get_class($this)])) {
        $options->data[get_class($this)] = &$this->data;
      } else {
        $this->data = &$options->data[get_class($this)];
        $this->afterload();
        
      }
      return  true;
    } else {
      return parent::load();
    }
  }
  
  public function save() {
    global $options;
    if ($this->dbversion) {
      return $options->save();
    } else {
      return parent::save();
    }
  }
  
  public function loaditems(array $items) {
    global  $db;
    if (!dbversion) return;
    //исключить из загрузки загруженные посты
    $items = array_diff($items, array_keys($this->items));
    if (count($items) == 0) return;
    $list = implode(',', $items);
    $res = $db->query("select * from $this->thistable where id in ($list)");
    $res->setFetchMode (PDO::FETCH_ASSOC);
    foreach ($res as $item) {
      $this->items[$item['id']] = $item;
    }
  }
  
  public function select($where) {
    if (!$this->dbversion) $this->error('Select method must be called ffrom database version');
    if (      $items = $this->db->getitems($where)) {
      $result = array();
      foreach ($items as $item){
        $id = $item['id'];
        $result[] = $id;
        $this->items[$id] = $item;
      }
      return $result;
    }
    return false;
  }
  
  public function getcount() {
    if ($this->dbversion) {
      return $this->db->getcount();
    } else {
      return count($this->items);
    }
  }
  
  public function getitem($id) {
    if ($this->dbversion && !isset($this->items[$id])) $this->items[$id] = $this->db->getitem($id);
    if (isset($this->items[$id])) return $this->items[$id];
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
      $id = $this->db->findid("$name = ". dbquote($value));
      return $id ? $id : -1;
    }
    
    foreach ($this->items as $id => $item) {
      if ($item[$name] == $value) {
        return $id;
      }
    }
    return -1;
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

class tsingleitems extends titems {
  public static $instances;
  public $id;
  
  public static function instance($class, $id = 0) {
    global $classes;
    if (!isset(self::$instances)) self::$instances = array();
    if (isset(self::$instances[$class][$id]))     return self::$instances[$class][$id];
    $self = $classes->newinstance($class);
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