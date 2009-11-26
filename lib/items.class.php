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
$this->dbversion = dbversion;
    $this->addevents('added', 'deleted');
    $this->addmap('items', array());
    $this->addmap('autoid', 0);
  }

public function load() {
if ($this->dbversion) return true;
return parent::load();
}

public function save() {
if ($this->dbversion) return true;
return parent::save();
}

  
  public function getcount() {
    if ($this->dbversion) {
      return $this->db->getcount();
    } else {
      return count($this->items);
    }
  }
  
  public function getitem($id) {
//$this->items[$id] = $this->db->getitem($id);
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
if ($this->dbversion) return $this->db->idexists($id);
return isset($this->items[$id]);
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
  
}
?>