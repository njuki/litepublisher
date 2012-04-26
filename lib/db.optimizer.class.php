<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tdboptimizer extends tevents {
  public $childtables;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'db.optimizer';
    $this->addmap('childtables', array());
    $this->addevents('postsdeleted');
  }
  
  public function garbageposts($table) {
    $db = litepublisher::$db;
    $deleted = $db->res2id($db->query("select id from $db->prefix$table where id not in
    (select $db->posts.id from $db->posts)"));
    if (count($deleted) > 0) {
      $db->table = $table;
      $db->deleteitems($deleted);
    }
  }
  
  public function deletedeleted() {
    //posts
    $db = litepublisher::$db;
    $db->table = 'posts';
    $items = $db->idselect("status = 'deleted'");
    if (count($items) > 0) {
      $this->postsdeleted($items);
      $deleted = sprintf('id in (%s)', implode(',', $items));
      $db->exec("delete from $db->urlmap where id in
      (select idurl from $db->posts where $deleted)");
      
      foreach (array('posts', 'rawposts', 'pages', 'postsmeta')  as $table) {
        $db->table = $table;
        $db->delete($deleted);
      }
      
      foreach ($this->childtables as $table) {
        $db->table = $table;
        $db->delete($deleted);
      }
    }
    
    //comments
$db->table = 'comments';
$items = $db->idselect("status = 'deleted'");
      $deleted = sprintf('id in (%s)', implode(',', $items));
$db->delete($deleted);
$db->table = 'rawcomments');
$db->delete($deleted);

//divide one qury by parts
/*
    $db->exec("delete from $db->users where status = 'comuser' and id not in
    (select DISTINCT author from $db->comments)");
  */  

    $from = 0;
$db->table = 'users';
    while ($items = $db->res2id($db->query("select id from $db->users where status = 'comuser' limit $from, 200"))) {
$from += count($items);
$comusers= implode(',', $items);
      $deleted = $db->res2id($db->query("select author from $db->comments where

}
    //subscribtions
    $db->exec("delete from $db->subscribers where post not in (select id from $db->posts)");
    $db->exec("delete from $db->subscribers where item not in (select id from $db->comusers)");
  }
  
  public function optimize() {
    $this->deletedeleted();
    sleep(2);
    $man = tdbmanager::i();
    $man->optimize();
  }
  
}//class