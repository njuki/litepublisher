<?php

class tplugin extends tevents {
  
  protected function create() {
    parent::create();
    $this->basename=  'plugins' .DIRECTORY_SEPARATOR  . strtolower(get_class($this));
  }
  
}

?>