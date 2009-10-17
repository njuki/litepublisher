<?php

class tdbanager  {
  private $max_allowed_packet;
  
  public static function &instance() {
    return GetInstance(__class__);
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
  
  public function CreateTable($name, $struct) {
    return $this->exec("
    create table $this->prefix$name
    ($struct)
    DEFAULT CHARSET=utf8
    COLLATE = utf8_general_ci");
  }
  
  public function DeleteTable($name) {
    $this->exec("DROP TABLE IF EXISTS $this->prefix$name");
  }
  
  public function  DeleteAllTables( ) {
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
  
  public function GetDatabases() {
    if ($res = $this->query("show databases")) {
      return $this->res2array($res);
    }
    return false;
  }
  
  public function DatabaseExists($name) {
    if ($list = $this->GetDatabaseList()) {
      return in_array($name, $list);
    }
    return FALSE;
  }
  
  public function GetTables() {
    global $dbconfig;
  if ($res = $this->query("show tables from {$dbconfig['dbname']} like '$this->prefix%'")) {
      return $this->res2array($res);
    }
    return false;
  }
  
  public function  TableExists( $name) {
    if ( $list = $this->GetTableList()) {
      return in_array($this->prefix . $name, $list);
    }
    return false;
  }
  
  public function CreateDatabase($name) {
    if ( $this->DatabaseExists($name) )  return false;
    return $this->exec("CREATE DATABASE $name");
  }

public function optimize() {
    $tables = $this->GetTables();
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
    
    $tables = $this->GetTables();
    foreach ($tables as $table) {
      $result .= $this->ExportTable($table);
    }
    $result .= "\n-- Lite Publisher dump end\n";
    return $result;
  }
  
  public function ExportTable($name) {
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
            $values[] = is_null($value) ? 'NULL' : $db->quote($value);
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
        $result .= "/*!40000 ALTER TABLE `$name` ENABLE KEYS */;\nUNLOCK TABLES;\n";
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
  
}//class
?>