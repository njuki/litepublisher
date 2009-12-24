<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tdbmanager  {
  private $max_allowed_packet;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function __get($name) {
    global $db;
    if ($name == 'db') return $db;
    return $db->$name;
  }
  
  public function __call($name, $arg) {
    global $db;
    return call_user_func_array(array(&$db, $name), $arg);
  }
  
  public function createtable($name, $struct) {
    if (defined('debug')) $this->deletetable($name);
    return $this->exec("
    create table $this->prefix$name
    ($struct)
    DEFAULT CHARSET=utf8
    COLLATE = utf8_general_ci");
  }
  
  public function deletetable($name) {
    $this->exec("DROP TABLE IF EXISTS $this->prefix$name");
  }
  
  public function  deletealltables( ) {
    global $dbconfig;
  $res = $this->query("show tables from {$dbconfig['dbname']}");
    $sql = '';
    while ($row = $res->fetch()) {
      //if (array_key_exists($row[0],$th)) do_export_table($row[0],1,$MAXI);
      $sql .= "drop table $name;\n";
    }
    return $this->exec($sql);
  }
  
  public function clear($name) {
    return $this->exec("truncate $this->prefix$name");
  }
  
  public function alter($arg) {
    return $this->exec("alter table $this->prefix$this->table $arg");
  }
  
  public function getdatabases() {
    if ($res = $this->query("show databases")) {
      return $this->res2id($res);
    }
    return false;
  }
  
  public function dbexists($name) {
    if ($list = $this->GetDatabaseList()) {
      return in_array($name, $list);
    }
    return FALSE;
  }
  
  public function gettables() {
    global $options;
    if ($res = $this->query(sprintf("show tables from %s like '%s%%'", $options->dbconfig['dbname'], $options->dbconfig['prefix']))) {
      return $this->res2id($res);
    }
    return false;
  }
  
  public function  TableExists( $name) {
    if ( $list = $this->GetTableList()) {
      return in_array($this->prefix . $name, $list);
    }
    return false;
  }
  
  public function createdatabase($name) {
    if ( $this->dbexists($name) )  return false;
    return $this->exec("CREATE DATABASE $name");
  }
  
  private function DeleteDeleted() {
    global $db;
    //posts
    $db->table = 'posts';
    $deleted = $db->idselect("status = 'deleted'");
    if (count($deleted) > 0) {
      $deleted = implode(',', $deleted);
      $db->exec("delete from $db->urlmap where id in
      (select idurl from $db->posts where status = 'deleted')");
      
      $db->exec("delete from $db->rawposts where id in ($selected)");
      
      $db->exec("delete from $db->pages where id in ($deleted)");
      
      $db->exec("delete from $db->posts where id in ($selected)");
    }
    
    //comments
    $db->exec("delete from $db->rawcomments where id in
    (select id from $db->comments where status = 'deleted')");
    
    $db->exec("delete from $db->comments where status = 'deleted'");
    
    $db->exec("delete from $db->authors where id not in
    (select DISTINCT author from $db->comments)");
    
    //subscribtions
    $db->exec("delete from$db->subscribers where post not in (select id from $db->posts)");
    $db->exec("delete from$db->subscribers where author not in (select id from $db->comusers)");
  }
  
  public function optimize() {
    $this->DeleteDeleted();
    $tables = $this->gettables();
    foreach ($tables as $table) {
      $this->exec("optimize $table");
    }
  }
  
  public function export() {
    global $options;
    $res = $this->query("show variables like 'max_allowed_packet'");
    $v = $res->fetch();
    $this->max_allowed_packet =floor($v['Value']*0.8);
    
    $result = "-- Lite Publisher dump $options->version\n";
    $result .= "-- Datetime: ".date('Y-m-d H:i:s') . "\n";
  $result .= "-- Host: {$options->dbconfig['host']}\n";
  $result .= "-- Database: {$options->dbconfig['dbname']}\n\n";
    $result .= "/*!40030 SET max_allowed_packet=$this->max_allowed_packet */;\n\n";
    
    $tables = $this->gettables();
    foreach ($tables as $table) {
      $result .= $this->exporttable($table);
    }
    $result .= "\n-- Lite Publisher dump end\n";
    return $result;
  }
  
  public function exporttable($name) {
    global $db;
    
    if ($res = $this->query("show create table `$name`")) {
      $row=$res->fetch();
      $result = "DROP TABLE IF EXISTS `$name`;\n$row[1];\n\n";
      if ($res =$this->query("select * from `$name`")) {
        $result .= "LOCK TABLES `$name` WRITE;\n/*!40000 ALTER TABLE `$name` DISABLE KEYS */;\n";
        $sql = '';
        while ($row = $res->fetch(PDO::FETCH_NUM)) {
          $values= array();
          foreach($row as $v){
            $values[] = is_null($v) ? 'NULL' : $db->quote($v);
          }
          $sql .= $sql ? ',(' : '(';
          $sql .= implode(', ', $values);
          $sql .= ')';
          
          if (strlen($sql)>$this->max_allowed_packet) {
            $result .= "INSERT INTO `$name` VALUES ". $sql . ";\n";
            $sql = '';
          }
        }
        
        if ($sql) $result .= "INSERT INTO `$name` VALUES ". $sql . ";\n";
        $result .= "/*!40000 ALTER TABLE `$name` ENABLE KEYS */;\nUNLOCK TABLES;\n\n";
      }
      return $result;
    }
  }
  
  public function import(&$dump) {
    global $db;
    $sql = '';
    $i = 0;
    while ($j = strpos($dump, "\n", $i)) {
      $s = substr($dump, $i, $j - $i);
      $i = $j + 1;
      if ($this->iscomment($s)) continue;
      $sql .= $s . "\n";
      if ($s[strlen($s) - 1] != ';') continue;
      $db->exec($sql);
      $sql = '';
    }
    
    $s = substr($dump, $i);
    if (!$this->iscomment($s))  $sql .= $s;
    if ($sql != '') $db->exec($sql);
  }
  
  private function iscomment(&$s) {
    if (strlen($s) <= 2) return true;
  $c2 = $s{1};
  switch ($s{0}) {
      case '/': return $c2 == '*';
      case '-': return $c2 == '-';
      case '#': return true;
    }
    return false;
  }
  
  public function performance() {
    global $db;
    $result = '';
    $total = 0.0;
    $max = 0.0;
    foreach ($db->history as $i => $item) {
      list($usec2, $sec2) = explode(' ', $item['started']);
      list($usec1, $sec1) = explode(' ', $item['finished']);
      $worked = round(($usec1 + $sec1) - ($usec2 + $sec2), 8);
      $total += $worked;
      if ($max < $worked) {
        $maxsql = $item['sql'];
        $max = $worked;
      }
    $result .= "$i: $worked\n{$item['sql']}\n\n";
    }
    $result .= "maximum $max\n$maxsql\n";
    $result .= sprintf("%s total time\n%d querries\n\n", $total, count($db->history));
    
    return $result;
  }
}//class
?>