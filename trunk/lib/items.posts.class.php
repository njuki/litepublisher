<?php

class titemsposts extends titems {

  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'fileitems';
$this->table = 'fileitems';
  }

public function add($idpost, $iditem) {
if (dbversion) {
$this->db->add(array(
'post' => $idpost,
'item' => $ititem
$this->added();
));
} else {
if (!isset($this->items[$idpost]))  $this->items[$idpost] = array();
if (!in_array($iditem, $this->items[$idpost])) {
$this->items[$idpost][] =$iditem;
$this->save();
$this->added();
return true;
}
return false;
}
}


public function delete($idpost, $iditem) {
if (dbversion) {
return $this->db->delete("post = $idpost and item = $iditem");
} elseif (isset($this->items[$idpost])) {
    $i = array_search($iditem, $this->items[$idpost]);
    if (is_int($i))  {
array_splice($this->items[$idpost], $i, 1);
$this->save();
$this->deleted();
return true;
}
return false;
}
}

public function deletepost($idpost) {
if (dbversion) {
$result = $this->db->res2array($this->db->query("select item from $this->thistable where post = $idpost"));
$this->db->delete("post = $idpost");
return $result;
} else {
if (isset($this->items[$idpost])) {
$result = $this->items[$idpost];
unset($this->items[$idpost]);
$this->save();
return $result;
} else {
return array();
}
}
}

public function deleteitem($iditem) {
if (dbversion) {
$this->db->delete("item = $iditem");
} else {
foreach ($this->items as $idpost => $item) {
    $i = array_search($iditem, $item);
    if (is_int($i))  array_splice($this->items[$idpost], $i, 1);
    }
$this->save();
}
$this->deleted();
}

public function setitems($idpost, array $items) {
if (dbversion) {
$db = $this->db;
$old = $this->getitems($idpost);
$add = array_diff($items, $old));
$delete = array_diff($old, $items);

if (count($delete) > 0) {
$db->delete("post = $idpost and item in (" . implode(', ', $delete) . ')');
}

if (count($add) > 0) {
$vals = array();
foreach ($add as $iditem) {
$vals[]= "($idpost, $iditem)";
}
$db->exec("INSERT INTO `$this->thistable` ('post', 'item') values " . implode(',', $vals) );
}

return array_merge($old, $add);
} else {
$result = array_merge($this->items[$idpost], array_diff($items, $this->items[$idpost]));
$this->items[$idpost] = $items;
$this->save();
return $result;
}
}

public function getitems($idpost) {
if (dbversion) {
return $this->res2array($this->db->qery("select file from $this->thistable where post = $idpost"));
} elseif (isset($this->items[$idpost])) {
return $this->items[$idpost];
} else {
return false;
}
}

public function getposts($iditem) {
if (dbversion) {
return $this->res2array($this->db->qery("select post from $this->thistable where file = $iditem"));
} else {
$result = array();
foreach ($this->items as $id => $item) {
if (in_array($iditem, $item)) $result[] = $id;
}
return $result;
}

public function getpostscount($ititem) {
$items = $this->getposts($iditem);
$posts = tposts::instance()
$items = $posts->stripdrafts($items);
return count($items);
}

public function updateposts(array $list, $propname) {
if (dbversion) {
$db = $this->db;
foreach ($list as $idpost) {
$items = $this->getitems($idpost);
$db->table = 'posts';
$db->setvalue($idpost, $Propname, implode(', ', $items));
}
} else {
foreach ($list as $idpost) {
$items = $this->items[$idpost];
$post = tpost::instance($idpost);
      if ($items != $post->$propname) {
        $post->$propname = $items;
          $post->Save();
      }
}
}

}//class

class titemspostsowner extends titemsposts {
private $owner;
 public function __construct($owner) {
parent::__construct();
$this->owner = $owner;
$this->items = &$owner->data['itemsposts'];
$this->table = $owner->table . 'items';
unset($this->data);
}

public function load() { }
public function save() { $this->owner->save(); }
public function lock() { $this->owner->lock(); }
public function unlock() { $this->owner->unlock(); }

}//class

?>
