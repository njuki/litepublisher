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

public function add($classname, $parent, $depended, $interfaces) {
$this->items[$classname] = array(
'parent' => $parent,
'depended' => $this->getclasses($depended),
'interfaces' => $this->getclasses($interfaces)
);

$this->save();
}

public function getchilds($parent) {
$result = array();
foreach ($this->items as $class => $item) {
if ($parent == $item['parent']) $result[] "[[$class]]";
}
return implode(', ', $result);
}

public function getclasses($s) {
$result = array();
foreach (explode(',', $s) as $classname) {
$classname = trim($classname);
if ($classname == '') continue;
$result[] = $classname;
}
return array_unique($result);
}

public function getuse($what, $where) {
$result = array();
foreach ($this->items as $class => $item) {
if (in_array($what, $item[$key])) $result[] = "[[$class]]";
}
return implode(', ', $result);
}

}//class