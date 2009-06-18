<?php

class TItems extends TEventClass {
 public $items;
 protected $lastid;
 
 protected function CreateData() {
  parent::CreateData();
  $this->AddEvents('Added', 'Deleted');
  $this->AddDataMap('items', array());
  $this->AddDataMap('lastid', 0);
 }
 
 public function Getcount() {
  return count($this->items);
 }
 
 public function GetItem($id) {
  if (isset($this->items[$id])) {
   return $this->items[$id];
  }
  return $this->Error("Item $id not found in class ". get_class($this));
 }
 
 public function GetValue($id, $name) {
  return $this->items[$id][$name];
 }
 
 public function SetValue($id, $name, $value) {
  $this->items[$id][$name] = $value;
 }
 
 public function ItemExists($id) {
  return isset($this->items[$id]);
 }
 
 public function IndexOf($name, $value) {
  foreach ($this->items as $id => $item) {
   if ($item[$name] == $value) {
    return $id;
   }
  }
  return -1;
 }
 
 public function Delete($id) {
  if (isset($this->items[$id])) {
   unset($this->items[$id]);
   $this->Save();
   $this->Deleted($id);
   return true;
  }
  return false;
 }
 
}
?>