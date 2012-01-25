<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tpermgroups extends tperm {

protected function create() {
parent::create();
$this->data['groups'] = array();
}

public function getheader($obj) {
$groups = implode("', '", $this->groups);
return sprintf('<?php if (!in_array(litepublisher::$options->group, array(\'%s\')) return litepublisher::$urlmap->forbidden(); ?>',  $groups);
}

public function setgroups($a) {
if (is_string($a)) $a = explode(',', $a);
$g = array('admin');
foreach ($a as $name) {
$name = trim($name);
if ($name == '') continue;
$g[] = $name;
}

$this->data['groups'] = array_unique($g);
$this->save();
}

}//class