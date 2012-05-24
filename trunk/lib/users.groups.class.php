<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tusergroups extends titems {
public $defaults;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'usergroups';
    $this->addmap('defaults', array());
    $this->addevents('onhasright');
  }
  
  public function add($name, $title, $home) {
    if ($id = $this->getidgroup($name)) return $id;
    $this->items[++$this->autoid] = array(
    'name' => $name,
    'title' => $title,
    'home' => $home,
'parents' => array()
    );
    $this->save();
    return $this->autoid;
  }
  
  public function delete($id) {
    if (!isset($this->items[$id])) return false;
    unset($this->items[$id]);
    $this->save();
    
    $users = tusers::i();
      $db = $users->db;
      $items = $db->res2assoc($users->getdb($users->grouptable)->select("idgroup = $id"));
      $users->getdb($users->grouptable)->delete("idgroup = $id");
      foreach ($items as $item) {
        $iduser = $item['iduser'];
        $idgroups = $db->res2id($db->query("select idgroup from $db->prefix$users->grouptable where iduser = $iduser"));
        $users->db->setvalue($iduser, 'idgroups', implode(',', $idgroups));
      }
  }

public function save() {
parent::save(); $this->update();
    if ($this->lockcount == 0) $this->update();
}

public function update() {
litepublisher::$options->data['groupnames'] = array();
$groupnames = &litepublisher::$options->data['groupnames'];
litepublisher::$options->data['parentgroups'] = array();
$parentgroups = &litepublisher::$options->data['parentgroups'];

foreach ($this->items as $id => $group) {
$names = explode(',', $group['name']);
foreach ($names as $name) {
if ($name = trim($name)) $groupnames[$name] = $id;
}
$parentgroups[$id] = $group['parents'];
}
litepublisher::$options->save();
  }
  
    public function getidgroup($name) {
$name = trim($name);
    return isset(litepublisher::$options->groupnames[$name]) ? litepublisher::$options->groupnames[$name] : false;
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
      
      case 'author':
      if ($group == 'commentator') return true;
      break;
      
      case 'subeditor':
      if (in_array($group, array('author', 'subscriber', 'moderator'))) return true;
      break;
    }
    
    if ($this->onhasright($who, $group)) return true;
    return false;
  }
  
  public function gethome($name) {
    if ($id = $this->cleangroup($name)) {
      return isset($this->items[$id]['home']) ? $this->items[$id]['home'] : '/admin/';
    }
    return '/admin/';
  }
  
}//class