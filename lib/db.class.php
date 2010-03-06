<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tdatabase {
  public $result;
  public $sql;
  public $table;
  public $prefix;
  public $history;
  public $handle;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function __construct() {
    if (!isset(litepublisher::$options->dbconfig)) return false;
    $dbconfig = litepublisher::$options->dbconfig;
    $this->table = '';
    $this->prefix =  $dbconfig['prefix'];
    $this->sql = '';
    $this->history = array();
    
    $host= $dbconfig['host'];
    if ($dbconfig['port'] > 0) $host .= ':' . $dbconfig['port'];
    $this->handle = @mysql_connect($host, $dbconfig['login'], str_rot13(base64_decode($dbconfig['password'])));
    if (! $this->handle) {
      die(@mysql_error());
    }
    if (!@        mysql_select_db($dbconfig['dbname'], $this->handle)) {
      die(@mysql_error($this->handle));
    }
    
    $this->exec('SET NAMES utf8');
    
    /* lost performance
    $timezone = date('Z') / 3600;
    if ($timezone > 0) $timezone = "+$timezone";
    $this->exec("SET time_zone = '$timezone:00'");
    */
  }
  
  public function __destruct() {
    if ($this->handle) @mysql_close($this->handle);
    $this->handle = false;
  }
  
  public function __get ($name) {
    return $this->prefix . $name;
  }
  
  public function query($sql) {
    return $this->doquery($sql, true);
  }
  
  public function exec($sql) {
    return $this->doquery($sql, false);
  }
  
  private function doquery($sql, $isquery) {
    /*
    if ($sql == $this->sql) {
      if ($this->result && @mysql_num_rows($this->result)) mysql_data_seek($this->result, 0);
      return $this->result;
    }
    */
    $this->sql = $sql;
    if (litepublisher::$debug) {
      $this->history[] = array(
      'sql' => $sql,
      'started' => microtime(),
      'finished' => microtime()
      );
    }
    
    if ($this->result)  {
      @mysql_free_result($this->result);
    }
    
    $this->result = @mysql_query($sql, $this->handle);
    if (litepublisher::$debug) {
      $this->history[count($this->history) - 1]['finished'] = microtime();
    }
    if ($this->result == false) {
      $this->doerror(@mysql_error($this->handle));
    }
    return $this->result;
  }
  
  private function doerror($mesg) {
    if (litepublisher::$debug) {
      $log = "exception:\n$mesg\n$this->sql\n";
      try {
        throw new Exception();
      } catch (Exception $e) {
        $log .=str_replace(litepublisher::$paths->home, '', $e->getTraceAsString());
      }
      $man = tdbmanager::instance();
      $log .= $man->performance();
      $log = str_replace("\n", "<br />\n", htmlspecialchars($log));
      die($log);
    } else {
      litepublisher::$options->trace($mesg);
    }
  }
  
  public function quote($s) {
    return "'" . mysql_real_escape_string($s, $this->handle) . "'";
  }
  
  public function select($where) {
    if ($where != '') $where = 'where '. $where;
    return $this->query("SELECT * FROM $this->prefix$this->table $where");
  }
  
  public function idselect($where) {
    return $this->res2id($this->query("select id from $this->prefix$this->table where $where"));
  }
  
  public function queryassoc($sql) {
    if ($r = mysql_fetch_assoc($this->query($sql))) return $r;
    return false;
  }
  
  public function getassoc($where) {
    return mysql_fetch_assoc($this->select($where));
  }
  
  public function update($values, $where) {
    return $this->exec("update $this->prefix$this->table set " . $values  ." where $where");
  }
  
  public function idupdate($id, $values) {
    return $this->update($values, "id = $id");
  }
  
  public function updateassoc($a) {
    $list = array();
    foreach ($a As $name => $value) {
      if ($name == 'id') continue;
      if (is_bool($value)) {
        $value =$value ? '1' : '0';
        $list[] = "$name = " . $value;
        continue;
      }
      $list[] = "$name = " . $this->quote($value);
    }
    
    return $this->update(implode(', ', $list), 'id = '. $a['id']);
  }
  
  public function UpdateProps($obj, $props) {
    $list = array();
    foreach ($props  As $name) {
      if ($name == 'id') continue;
      $list[] = "$Name = " . $this->quote($obj->$name);
    }
    
    return $this->update(implode(', ', $list), "id = $obj->id");
  }
  
  public function insertrow($row) {
    $this->exec("INSERT INTO $this->prefix$this->table $row");
    return @mysql_insert_id($this->handle);
  }
  
  public function insertassoc(array $a) {
    unset($a['id']);
    return $this->add($a);
  }
  
  public function insert(array $a) {
    if ($this->idexists($a['id'])) {
      $this->updateassoc($a);
    } else {
      $this->add($a);
    }
  }
  
  public function add(array $a) {
    $Names =implode(', ', array_keys($a));
    $vals = array();
    foreach( $a as $name => $val) {
      if (is_bool($val)) {
        $vals[] = $val ? '1' : '0';
      } else {
        $vals[] = $this->quote($val);
      }
    }
    
    $this->exec("INSERT INTO $this->prefix$this->table ($Names) values (" . implode(', ', $vals) . ')');
    return @mysql_insert_id($this->handle);
  }
  
  public function getcount($where = '') {
    $sql = "SELECT COUNT(*) as count FROM $this->prefix$this->table";
    if ($where != '') $sql .= ' where '. $where;
    if ($r = mysql_fetch_assoc( $this->query($sql))) {
      return $r['count'];
    }
    return false;
  }
  
  public function delete($where) {
    return $this->exec("delete from $this->prefix$this->table where $where");
  }
  
  public function iddelete($id) {
    return $this->exec("delete from $this->prefix$this->table where id = $id");
  }
  
  public function deleteitems(array $items) {
    return $this->delete('id in ('. implode(', ', $items) . ')');
  }
  
  public function idexists($id) {
    if ($r = mysql_fetch_assoc($this->query("select id  from $this->prefix$this->table where id = $id limit 1"))) return true;
    return false;
  }
  
  public function  exists($where) {
    if (mysql_num_rows($this->query("select *  from $this->prefix$this->table where $where limit 1"))) return true;
    return false;
  }
  
  public function getlist(array $list) {
    return $this->res2assoc($this->select(sprintf('id in (%s)', implode(',', $list))));
  }
  
  public function getitems($where) {
    return $this->res2assoc($this->select($where));
  }
  
  public function getitem($id) {
    return mysql_fetch_assoc($this->query("select * from $this->prefix$this->table where id = $id limit 1"));
  }
  
  public function finditem($where) {
    return mysql_fetch_assoc($this->query("select * from $this->prefix$this->table where $where limit 1"));
  }
  
  public function findid($where) {
    if($r = mysql_fetch_assoc($this->query("select id from $this->prefix$this->table where $where limit 1"))) return $r['id'];
    return false;
  }
  
  public function getvalue($id, $name) {
    if ($r = mysql_fetch_assoc($this->query("select $name from $this->prefix$this->table where id = $id limit 1"))) return $r[$name];
    return false;
  }
  
  public function setvalue($id, $name, $value) {
    return $this->update("$name = " . $this->quote($value), "id = $id");
  }
  
  public function res2array($res) {
    $result = array();
    while ($row = mysql_fetch_row($res)) {
      $result[] = $row;
    }
    return $result;
  }
  
  public function res2id($res) {
    $result = array();
    while ($row = mysql_fetch_row($res)) {
      $result[] = $row[0];
    }
    return $result;
  }
  
  public function res2assoc($res) {
    $result = array();
    while ($r = mysql_fetch_assoc($res)) {
      $result[] = $r;
    }
    return $result;
  }
  
  public function fetchassoc($res) {
    return mysql_fetch_assoc($res);
  }
  
  public function fetchnum($res) {
    return mysql_fetch_row($res);
  }
  
  public function countof($res) {
    return  mysql_num_rows($res);
  }
  
}//class
?>