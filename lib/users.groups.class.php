<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tusergroups extends titems {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'usergroups';
    $this->data['defaultgroup'] = 'nobody';
    $this->addevents('onhasright');
  }
  
  public function add($name, $title, $home) {
    if ($id = $this->groupid($name)) return $id;
    $this->items[++$this->autoid] = array(
    'name' => $name,
'title' => $title,
    'home' => $home
    );
    $this->save();
    return $this->autoid;
  }
  
  public function groupid($name) {
return $this->IndexOf('name', trim($name));
  }

public function cleangroup($v) {
if (is_string($v)) $v = trim($v);
    if (is_numeric($v)) {
      $id = (int) $v;
      if ($this->itemexists($id)) return $id;
} else {
return $this->groupid($v);
}
return false;
}

public function cleangroups($v) {
if (is_array($v)) return $this->checkgroups(array_unique($v));

if(is_string($v)) {
$v = trim($v);
if (strpos($v, ',')) {
return $this->checkgroups(explode(',', $v));
}
}
if ($id = $this->cleangroup($v)) return array($id);

}

protected function checkgroups(array $a) {
$result = array();
foreach ($a as $val) {
if ($id = $this->cleangroup($val)) $result[] = $id;
}

return array_unique($result);
}
  
  public function hasright($who, $group) {
    if ($who == $group) return  true;
    if (($who == 'admin') || ($group == 'nobody')) return true;
    switch ($who) {
      case 'editor':
      if ($group == 'author') return true;
      break;
      
      case 'moderator':
      if (($group == 'subscriber') || ($group == 'author')) return true;
      break;
      
      case 'subeditor':
      if (in_array($group, array('author', 'subscriber', 'moderator'))) return true;
      break;
    }
    
    if ($this->onhasright($who, $group)) return true;
    return false;
  }
  
  public function gethome($name) {
    if ($id = $this->groupid($name)) {
      return isset($this->items[$id]['home']) ? $this->items[$id]['home'] : '/admin/';
    }
    return '/admin/';
  }
  
}//class