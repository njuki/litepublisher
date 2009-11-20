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
  
  protected function create() {
    parent::create();
    $this->addevents('added', 'deleted');
    $this->addmap('items', array());
    $this->addmap('autoid', 0);
  }
  
  public function getcount() {
    if (dbversion) {
      return $this->db->getcount();
    } else {
      return count($this->items);
    }
  }
  
  public function getItem($id) {
    if (dbversion && !isset($this->items[$id])) $this->items[$id] = $this->db->getitem($id);
        if (isset($this->items[$id])) return $this->items[$id];
    return $this->error("Item $id not found in class ". get_class($this));
  }
  
  public function getvalue($id, $name) {
    if (dbversion && !isset($this->items[$id])) $this->items[$id] = $this->db->getitem($id);
    return $this->items[$id][$name];
  }
  
  public function setvalue($id, $name, $value) {
    $this->items[$id][$name] = $value;
if (dbversion) {
$this->db->setvalue($id, $name, $value);
}
  }
  
  public function itemexists($id) {
if (dbversion) return $this->db->idexists($id);
return isset($this->items[$id]);
  }
  
  public function IndexOf($name, $value) {
if (dbversion){
$id = $this->db->findid($name, $value);
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
    if (dbversion) $this->db->delete("id = $id");
     if (isset($this->items[$id])) {
        unset($this->items[$id]);
        if (!dbversion) $this->save();
        $this->deleted($id);
        return true;
      }
      return false;
  }
  
}
?>