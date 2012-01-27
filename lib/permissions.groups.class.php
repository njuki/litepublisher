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
$this->adminclass = 'tadminpermgroups';
$this->data['groups'] = array();
}

public function getheader($obj) {
if (count($this->groups) == 0) return '';
$groups = implode("', '", $this->groups);
return sprintf('<?php if (!in_array(litepublisher::$options->group, array(\'%s\')) return litepublisher::$urlmap->forbidden(); ?>',  $groups);
}

//class