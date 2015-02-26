<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
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
  
  public static function i() {
    return getinstance(__class__);
  }
  
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
    $this->handle = mysql_connect($host, $dbconfig['login'], str_rot13(base64_decode($dbconfig['password'])));
    if (! $this->handle) {
      //die(mysql_error());
      throw new Exception('Error connect to database');
    }
    if (!        mysql_select_db($dbconfig['dbname'], $this->handle)) {
      throw new Exception('Error select database');
    }
    
    $this->query('SET NAMES utf8');
    
    /* lost performance
    $timezone = date('Z') / 3600;
    if ($timezone > 0) $timezone = "+$timezone";
    $this->query("SET time_zone = '$timezone:00'");
    */
  }
  
  /*
  public function __destruct() {
    if (is_object($this)) {
      if (is_resource($this->handle)) mysql_close($this->handle);
      $this->handle = false;
    }
  }
  */
  
  public function __get ($name) {
    return $this->prefix . $name;
  }
  
  public function  exec($sql) {
    return $this->query($sql);
  }
  
  public function query($sql) {
    /*
    if ($sql == $this->sql) {
      if ($this->result && @mysql_num_rows($this->result)) mysql_data_seek($this->result, 0);
      return $this->result;
    }
    */
    //if (strbegin($sql, 'select ')) $sql = str_replace('select ', 'select SQL_BUFFER_RESULT ', $sql);
    $this->sql = $sql;
    if (litepublisher::$debug) {
      $this->history[] = array(
      'sql' => $sql,
      'time' => 0
      );
      $microtime = microtime(true);
    }
    
    if (is_resource ($this->result)) mysql_free_result($this->result);
    $this->result = mysql_query($sql, $this->handle);
    if (litepublisher::$debug) {
      $this->history[count($this->history) - 1]['time'] = microtime(true) - $microtime;
      if ($r = mysql_fetch_assoc(mysql_query('SHOW WARNINGS', $this->handle))) {
        echo "<pre>\n";
        echo $sql, "\n";
        var_dump($r);
        echo "</pre>\n";
      }
    }
    if ($this->result == false) {
      $this->doerror(mysql_error($this->handle));
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
      $man = tdbmanager::i();
      $log .= $man->performance();
      $log = str_replace("\n", "<br />\n", htmlspecialchars($log));
      die($log);
    } else {
      litepublisher::$options->trace($this->sql . "\n" . $mesg);
    }
  }
  
  public function quote($s) {
    return sprintf('\'%s\'', mysql_real_escape_string($s));
  }
  
  public function escape($s) {
    return mysql_real_escape_string($s);
  }
  
  public function settable($table) {
    $this->table = $table;
    return $this;
  }
  
  public function select($where) {
    if ($where != '') $where = 'where '. $where;
    return $this->query("SELECT * FROM $this->prefix$this->table $where");
  }
  
  public function idselect($where) {
    return $this->res2id($this->query("select id from $this->prefix$this->table where $where"));
  }
  
  public function selectassoc($sql) {
    return mysql_fetch_assoc($this->query($sql));
  }
  
  public function getassoc($where) {
    return mysql_fetch_assoc($this->select($where));
  }
  
  public function update($values, $where) {
    return $this->query("update $this->prefix$this->table set " . $values  ." where $where");
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
        $list[] = sprintf('%s = %s ', $name, $value);
        continue;
        /*
      } elseif (is_array($value)) {
        $list[] = sprintf('%s = \'%s\'', $name, implode('\',\'', $value));
        continue;
        */
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
    return $this->query(sprintf('INSERT INTO %s%s %s', $this->prefix, $this->table, $row));
  }
  
  public function insertassoc(array $a) {
    unset($a['id']);
    return $this->add($a);
  }
  
  public function insert(array $a) {
    if ($this->idexists($a['id'])) {
      $this->updateassoc($a);
    } else {
      return $this->add($a);
    }
  }
  
  public function add(array $a) {
    $this->insertrow($this->assoctorow($a));
    if ($id = mysql_insert_id($this->handle)) return $id;
    $r = mysql_fetch_row($this->query('select last_insert_id() from ' . $this->prefix . $this->table));
    return (int) $r[0];
  }
  
  public function insert_a(array $a) {
    $this->insertrow($this->assoctorow($a));
  }
  
  public function assoctorow(array $a) {
    $vals = array();
    foreach( $a as $name => $val) {
      if (is_bool($val)) {
        $vals[] = $val ? '1' : '0';
      } else {
        $vals[] = $this->quote($val);
      }
    }
    return sprintf('(%s) values (%s)', implode(', ', array_keys($a)), implode(', ', $vals));
  }
  
  public function getcount($where = '') {
    $sql = "SELECT COUNT(*) as count FROM $this->prefix$this->table";
    if ($where != '') $sql .= ' where '. $where;
    if ($r = mysql_fetch_assoc( $this->query($sql))) {
      return (int) $r['count'];
    }
    return false;
  }
  
  public function delete($where) {
    return $this->query("delete from $this->prefix$this->table where $where");
  }
  
  public function iddelete($id) {
    return $this->query("delete from $this->prefix$this->table where id = $id");
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
  
  public function getval($table, $id, $name) {
    if ($r = mysql_fetch_assoc($this->query("select $name from $this->prefix$table where id = $id limit 1"))) return $r[$name];
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
    if (is_resource($res)) {
      while ($row = mysql_fetch_row($res)) {
        $result[] = $row;
      }
      return $result;
    }
  }
  
  public function res2id($res) {
    $result = array();
    if (is_resource($res)) {
      while ($row = mysql_fetch_row($res)) {
        $result[] = $row[0];
      }
    }
    return $result;
  }
  
  public function res2assoc($res) {
    $result = array();
    if (is_resource($res)) {
      while ($r = mysql_fetch_assoc($res)) {
        $result[] = $r;
      }
    }
    return $result;
  }
  
  public function res2items($res) {
    $result = array();
    if (is_resource($res)) {
      while ($r = mysql_fetch_assoc($res)) {
        $result[(int) $r['id']] = $r;
      }
    }
    return $result;
  }
  
  public function fetchassoc($res) {
    return is_resource($res) ? mysql_fetch_assoc($res) : false;
  }
  
  public function fetchnum($res) {
    return is_resource($res) ? mysql_fetch_row($res) : false;
  }
  
  public function countof($res) {
    return  is_resource($res) ? mysql_num_rows($res) : 0;
  }
  
  public static function str2array($s) {
    $result = array();
    foreach (explode(',', $s) as $i => $value) {
      $v = (int) trim($value);
      if ($v== 0) continue;
      $result[] = $v;
    }
    return $result;
  }
  
}//class
?>