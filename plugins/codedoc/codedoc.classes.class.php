<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tcodedocclasses extends titems {
  
  public static function i() {
    return getinstance(__class__);
  }

public function create() {
$this->dbversion = false;
parent::create();
$this->basename = 'codedoc.classes';
}

public function add($idpost, $classname, $parentclass, $depended, $interfaces) {
$this->items[$idpost] = array(
'parent' => $this->findpost($parentclass),
'childs' => $this->findchilds($idpost),
'depended' => $this->findclasses($depended),
'used' =>  $this->finduse($idpost, 'depended'),
'interfaces' => $this->findclasses($interfaces)
);

$this->save();
}

public function findchilds($id) {
$result = array();
foreach ($this->items as $idpost => $item) {
if ($id == $item['parent']) $result[] $idpost;
}
return $result;
}

public function findpost($classname) {
foreach ($this->items as $idpost => $item) {
if ($classname == $item['classname']) return $idpost;
}
return 0;
}

public function findclasses($s) {
$result = array();
foreach (explode(',', $s) as $classname) {
$classname = trim($classname);
if ($classname == '') continue;
if ($idpost = $this->findclass($classname)) $result[] = $idpost;
}
return array_unique($result);
}

public function finduse($id, $key) {
$result = array();
foreach ($this->items as $idpost => $item) {
if (in_array($id, $item[$key])) $result[] = $idpost;
}
return $result;
}

}//class