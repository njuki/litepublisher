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
    if ($name == 'db') return litepublisher::$db;
    return litepublisher::$db->$name;
  }
  
  public function __call($name, $arg) {
    return call_user_func_array(array(&litepublisher::$db, $name), $arg);
  }
  
  public function createtable($name, $struct) {
    //    if (litepublisher::$debug)
    //$this->deletetable($name);
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
    $list = $this->res2array($this->query("show tables from " . litepublisher::$options->dbconfig['dbname']));
    foreach ($list as $row) {
      $this->exec("DROP TABLE IF EXISTS ". $row[0]);
    }
  }
  
  public function clear($name) {
    return $this->exec("truncate $this->prefix$name");
  }
  
  public function alter($arg) {
    return $this->exec("alter table $this->prefix$this->table $arg");
  }
  
  public function setautoincrement($table, $value) {
    $this->exec("ALTER TABLE $this->prefix$table AUTO_INCREMENT = $value");
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
    if ($res = $this->query(sprintf("show tables from %s like '%s%%'", litepublisher::$options->dbconfig['dbname'], litepublisher::$options->dbconfig['prefix']))) {
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
  
  private function deletedeleted() {
    $posts = tposts::instance();
    $posts->deletedeleted();
    
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
    $prefix = strtolower(litepublisher::$options->dbconfig['prefix']);
    $tables = $this->gettables();
    foreach ($tables as $table) {
      if (strbegin(strtolower($table), $prefix)) $this->exec("OPTIMIZE TABLE $table");
    }
  }
  
  public function export() {
    $options = litepublisher::$options;
    $v = $this->fetchassoc($this->query("show variables like 'max_allowed_packet'"));
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
    if ($row=$this->fetchnum($this->query("show create table `$name`"))) {
      $result = "DROP TABLE IF EXISTS `$name`;\n$row[1];\n\n";
      $res =$this->query("select * from `$name`");
      if ($this->countof($res) > 0) {
        $result .= "LOCK TABLES `$name` WRITE;\n/*!40000 ALTER TABLE `$name` DISABLE KEYS */;\n";
        $sql = '';
        while ($row = $this->fetchnum($res)) {
          $values= array();
          foreach($row as $v){
            $values[] = is_null($v) ? 'NULL' : dbquote($v);
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
    $sql = '';
    $i = 0;
    while ($j = strpos($dump, "\n", $i)) {
      $s = substr($dump, $i, $j - $i);
      $i = $j + 1;
      if ($this->iscomment($s)) continue;
      $sql .= $s . "\n";
      if ($s[strlen($s) - 1] != ';') continue;
      litepublisher::$db->exec($sql);
      $sql = '';
    }
    
    $s = substr($dump, $i);
    if (!$this->iscomment($s))  $sql .= $s;
    if ($sql != '') litepublisher::$db->exec($sql);
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
    $result = '';
    $total = 0.0;
    $max = 0.0;
    foreach (litepublisher::$db->history as $i => $item) {
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
    $result .= sprintf("%s total time\n%d querries\n\n", $total, count(litepublisher::$db->history));
    
    return $result;
  }
}//class
?>