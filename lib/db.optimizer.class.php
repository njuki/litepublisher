<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tdboptimizer extends tevents {

    public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'db.optimizer';
}


  public function deletedeleted() {
//posts
    $deleted = $this->db->idselect("status = 'deleted'");
    if (count($deleted) == 0) return;
    $deleted = implode(',', $deleted);
    $db = litepublisher::$db;
    $db->exec("delete from $db->urlmap where id in
    (select idurl from $this->thistable where id in ($deleted))");
    
    $this->getdb($this->rawtable)->delete("id in ($deleted)");
    $this->getdb('pages')->delete("id in ($deleted)");
    
    $db->exec("delete from $db->postsmeta where id in ($deleted)");
    $this->db->delete("id in ($deleted)");

if ($this->childtable) {
    $db = $this->getdb($this->childtable);
    $childsdeleted = $db->res2id($db->query("select id from $db->prefix$this->childtable where id not in
    (select $db->posts.id from $db->posts)"));
    if (count($childsdeleted) > 0) {
    $db->table = $this->childtable;
    $db->deleteitems($childsdeleted);
}
}
  
    
    $db = litepublisher::$db;
    //comments
    $db->exec("delete from $db->rawcomments where id in
    (select id from $db->comments where status = 'deleted')");
    
    $db->exec("delete from $db->comments where status = 'deleted'");
    
    $db->exec("delete from $db->comusers where id not in
    (select DISTINCT author from $db->comments)");
    
    //subscribtions
    $db->exec("delete from $db->subscribers where post not in (select id from $db->posts)");
    $db->exec("delete from $db->subscribers where item not in (select id from $db->comusers)");
  }

  public function optimize() {  
    $this->deletedeleted();
    sleep(2);
$man = tdbmanager::instance();
$man->optimize();
}

}//class

?>>