<?php

class titemsposts extends TItems {

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
$this->db->delete("post = $idpost");
} elseif (isset($this->items[$idpost])) {
unset($this->items[$idpost]);
$this->save();
}
$this->deleted();
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

}//class

?>
