<?php

class tusergroups extends TItems {

  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'usergroups';
}

function add($name) {
$this->items[++$this->autoid] = array(
'name' => $name,
);
$this->save();
return $this->autoid;
}

public function groupid($name) {
foreach ($this->items as $id => $item) {
if ($name == $item['name']) return $id;
}
return false;
}

}//class
?>