<?php

class TRedirector extends TItems {
  public static function &Instance() {
    return GetInstance(__class__);
  }
  
  protected function CreateData() {
    parent::CreateData();
    $this->basename = 'redirector';
  }
  
  public function Add($from, $to) {
    $this->items[$from] = $to;
    $this->Save();
    $this->Added($from);
  }
  
}
?>