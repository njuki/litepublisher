<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tpols extends titems {
public $userstable;
public $resulttable;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = dbversion;
    parent::create();
    $this->table = 'pols';
    $this->basename = 'pols';
$this->userstable = 'polusers';
$this->resulttable = 'polsresult';'
  }
  
  public function add($url) {
    $id = $this->IndexOf('url', $url);
    if ($id > 0) return $id;
    $item = array(
    'url' => $url,
    'clicked' => 0
    );
    
    if ($this->dbversion) {
      $id = $this->db->add($item);
      $this->items[$id] = $item;
      return $id;
    } else {
      $this->items[++$this->autoid]  = $item;
      $this->save();
      return $this->autoid;
    }
  }
  
  public function createpol($id, &$content) {
while (is_int($i = strpos($content, '[pol]'))) {
$j = strpos($content, '[/pol]', $i);
if ($j === false) {
// значит простая форма и надо найти первую пустую строку

} else {
}
}
}

  public function filter(&$content) {
}

public function postdeleted($id) {
$list = $this->db->idselect("post = $id");
if (count($list) == 0) return;
$items = sprintf('(%s)', implode(',', $list));
$this->db->delete("id in $items");
$this->getdb($this->resulttable)->delete("id in $items");
$db = $this->getdb($this->userstable);
$db->delete("id not in (
select DISTINCT user from $db->prefix.$this->resulttable)");
}

public function xmlrpcpol($idpol, $vote) {
}

}//class
?>