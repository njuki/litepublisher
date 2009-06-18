<?php

class TPostContentPlugin extends TPlugin {

 public static function &Instance() {
  return GetInstance(__class__);
 }
 
 protected function CreateData() {
  parent::CreateData();
$this->Data['before'] = '';
$this->Data['after'] = '';
 }

public function BeforePostContent($id) {
return $this->before;
}

public function AfterPostContent($id) {
return $this->after;
}
 
}
?>