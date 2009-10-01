<?php
$dbconfig = parse_ini_file(dirname(__file__) . '\dbconfig.ini', false);

class TDatabase extends PDO {
  public $result;
  public $sql;
  public $table;
  public $prefix;
  public $history;
  
  public function __construct() {
    global $dbconfig;
    $this->table = '';
    $this->prefix =  $dbconfig['prefix'];
    $this->sql = '';
    $this->history = array();
    
    try {
parent::__construct("{$dbconfig['driver']}:host={$dbconfig['host']};dbname={$dbconfig['dbname']}", $dbconfig['login'], $dbconfig['password']);
    } catch (Exception $e) {
      die($e->getMessage());
    }
    $this->exec('SET NAMES utf8');
    //$this->exec("SET time_zone = '$Options->timezone'");
  }
  
  public function query($sql, $mode = null) {
    $this->sql = $sql;
    if (defined('debug')) $this->history[] = $sql;
    if (isset($this->result)) $this->result->closeCursor();
    $this->result = parent::query($sql, $mode);
    return $this->result;
  }
  
  public function SelectTableWhere($table, $where) {
    return $this->query("SELECT * FROM $this->prefix$table WHERE ($where)");
  }
  
  public function select($where) {
    return $this->query("SELECT * FROM $this->prefix$this->table WHERE  $where");
  }
  
  public function update($values, $where) {
    return $this->query("update $this->prefix$this->table set " . $values  ." where $where");
  }
  
  public function idupdate($id, $values) {
    return $this->update($values, "id = $id");
  }
  
  public function UpdateAssoc($a) {
    $id = $a['id'];
    unset($a['id']);
    $list = array();
    foreach ($a As $name => $value) {
      $list[] = "$Name = " . $this->quote($value);
    }
    
    return $this->update(implode(', ', $list), "id = $id");
  }
  
  public function InsertRow($row) {
    $this->exec("INSERT INTO $this->prefix$this->table $row");
    return $this->lastInsertId();
  }
  
  public function InsertAssoc(&$a) {
    $Names =implode(', ', array_keys($a));
    $vals = array();
    foreach( array_values($a) as $val) {
      $vals[] = $this->quote($val);
    }
    $Values = implode(', ', $vals);
    
    $this->exec("INSERT INTO $this->prefix$this->table ($Names) values (" . $Values . ')');
    return $this->lastInsertId();
  }
  
  public function add($a) {
    if  ($this->IdExists($a['id'])) {
      return $this->UpdateAssoc($a);
    } else {
      return $this->InsertAssoc($a);
    }
  }
  
}//class
?>