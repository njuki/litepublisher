<?php

class TDBManager  {
  
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
    $this->exec("
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
  
}//class
?>