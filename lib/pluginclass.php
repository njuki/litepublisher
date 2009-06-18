<?php

class TPlugin extends TEventClass {
 
 protected function CreateData() {
  parent::CreateData();
  $this->basename=  'plugins' .DIRECTORY_SEPARATOR  . strtolower(get_class($this));
 }
 
 public static function &Instance() {
  return GetInstance(__class__);
 }
 
}

?>