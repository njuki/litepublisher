<?php

class tredirector extends titems {
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'redirector';
  }
  
  public function add($from, $to) {
    $this->items[$from] = $to;
    $this->save();
    $this->added($from);
  }
  
}
?>