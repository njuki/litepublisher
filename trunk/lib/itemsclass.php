<?php

class TItems extends TEventClass {
  public $items;
  protected $lastid;
  
  protected function create() {
    parent::create();
    $this->AddEvents('added', 'deleted');
    $this->AddDataMap('items', array());
    $this->AddDataMap('lastid', 0);
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
  
  public function ItemExists($id) {
if (dbversion) return $this->db->idexists($id);
return isset($this->items[$id]);
  }
  
  public function IndexOf($name, $value) {
if (dbversion){
$id = $this->db->findid($name, $value));
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