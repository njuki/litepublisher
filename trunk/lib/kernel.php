<?php

//data.class.php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tdata {
  public static $savedisabled;
  public $basename;
  public $cache;
  public $coclasses;
  public $coinstances;
  public $data;
  public $lockcount;
  public $table;
  
  public function __construct() {
    $this->lockcount = 0;
    $this->cache= true;
    $this->data= array();
    $this->coinstances = array();
    $this->coclasses = array();
    $this->basename = substr(get_class($this), 1);
    $this->create();
  }
  
  protected function create() {
  }
  
  public function __get($name) {
    if (method_exists($this, $get = 'get' . $name))  {
      return $this->$get();
    } elseif (array_key_exists($name, $this->data)) {
      return $this->data[$name];
    } else {
      foreach ($this->coinstances as $coinstance) {
        if (isset($coinstance->$name)) return $coinstance->$name;
      }
      return    $this->error(sprintf('The requested property "%s" not found in class  %s', $name, get_class($this)));
    }
  }
  
  public function __set($name, $value) {
    if (method_exists($this, $set = 'set' . $name)) {
      $this->$set($value);
      return true;
    }
    
    if (key_exists($name, $this->data)) {
      $this->data[$name] = $value;
      return true;
    }
    
    foreach ($this->coinstances as $coinstance) {
      if (isset($coinstance->$name)) {
        $coinstance->$name = $value;
        return true;
      }
    }
    
    return false;
  }
  
  public  function __call($name, $params) {
    if (method_exists($this, strtolower($name))) {
      return call_user_func_array(array($this, strtolower($name)), $params);
    }
    
    foreach ($this->coinstances as $coinstance) {
      if (method_exists($coinstance, $name) || $coinstance->method_exists($name))
      return call_user_func_array(array($coinstance, $name), $params);
    }
    $this->error("The requested method $name not found in class " . get_class($this));
  }
  
  public function __isset($name) {
    if (array_key_exists($name, $this->data) || method_exists($this, "get$name") || method_exists($this, "Get$name")) return true;
    foreach ($this->coinstances as $coinstance) {
      if (isset($coinstance->$name)) return true;
    }
    return false;
  }
  
  public function method_exists($name) {
    return false;
  }
  
  public function error($Msg) {
    throw new Exception($Msg);
  }
  
  public function getbasename() {
    return $this->basename;
  }
  
  public function install() {
    $this->externalchain('Install');
  }
  
  public function uninstall() {
    $this->externalchain('Uninstall');
  }
  
  public function validate($repair = false) {
    $this->externalchain('Validate', $repair);
  }
  
  protected function externalchain($func, $arg = null) {
    $parents = class_parents($this);
    array_splice($parents, 0, 0, get_class($this));
    foreach ($parents as $key => $class) {
      $this->externalfunc($class, $func, $arg);
    }
  }
  
  protected function externalfunc($class, $func, $arg) {
    if ($filename = litepublisher::$classes->getclassfilename($class)) {
      $externalname = basename($filename, '.php') . '.install.php';
      $dir = dirname($filename) . DIRECTORY_SEPARATOR;
      $file = $dir . 'install' . DIRECTORY_SEPARATOR . $externalname;
      if (!file_exists($file)) {
        $file =$dir .  $externalname;
        if (!file_exists($file)) return;
      }
      
      include_once($file);
      $fnc = $class . $func;
      if (function_exists($fnc)) $fnc($this, $arg);
    }
  }
  
  public function load() {
    //if ($this->dbversion == 'full') return $this->LoadFromDB();
    return tfilestorage::load($this);
  }
  
  public function save() {
    if ($this->lockcount > 0) return;
    if ($this->dbversion) {
      $this->SaveToDB();
    } else {
      tfilestorage::save($this);
    }
  }
  
  public function savetostring() {
    return serialize($this->data);
  }
  
  public function loadfromstring($s) {
    try {
      if (!empty($s)) $this->data = unserialize($s) + $this->data;
      $this->afterload();
      return true;
    } catch (Exception $e) {
      echo 'Caught exception: '.  $e->getMessage() ;
      return false;
    }
  }
  
  public function afterload() {
    foreach ($this->coinstances as $coinstance) {
      $coinstance->afterload();
    }
  }
  
  public function lock() {
    $this->lockcount++;
  }
  
  public function unlock() {
    if (--$this->lockcount <= 0) $this->save();
  }
  
  public function getlocked() {
    return $this->lockcount  > 0;
  }
  
  public function Getclass() {
    return get_class($this);
  }
  
  public function getdbversion() {
    return false; // dbversion == 'full';
  }
  
  public function getdb($table = '') {
    $table =$table != '' ? $table : $this->table;
    if ($table != '') litepublisher::$db->table = $table;
    return litepublisher::$db;
  }
  
  protected function SaveToDB() {
    $this->db->add($this->getbasename(), $this->savetostring());
  }
  
  protected function LoadFromDB() {
    if ($r = $this->db->select('basename = '. $this->getbasename() . "'")) {
      return $this->loadfromstring($r['data']);
    }
  }
  
  protected function getthistable() {
    return litepublisher::$db->prefix . $this->table;
  }
  
}//class

class tfilestorage {
  public static $disabled;
  
  public static function save(tdata $obj) {
    if (self::$disabled) return false;
    return self::savetofile(litepublisher::$paths->data .$obj->getbasename(), self::comment_php($obj->savetostring()));
  }
  
  public static function load(tdata $obj) {
    $filename = litepublisher::$paths->data . $obj->getbasename() .'.php';
    if (file_exists($filename)) {
      return $obj->loadfromstring(self::uncomment_php(file_get_contents($filename)));
    }
    return false;
  }
  
  public static function savetofile($base, $content) {
    $tmp = $base .'.tmp.php';
    if(false === file_put_contents($tmp, $content)) {
      litepublisher::$options->trace("Error write to file $tmp");
      return false;
    }
    chmod($tmp, 0666);
    $filename = $base .'.php';
    if (file_exists($filename)) {
      $back = $base . '.bak.php';
      if (file_exists($back)) unlink($back);
      rename($filename, $back);
    }
    if (!rename($tmp, $filename)) {
      litepublisher::$options->trace("Error rename file $tmp to $filename");
      return false;
    }
    return true;
  }
  
  public static function comment_php($s) {
    return sprintf('<?php /* %s */ ?>', str_replace('*/', '**//*/', $s));
  }
  
  public static function uncomment_php($s) {
    return str_replace('**//*/', '*/', substr($s, 9, strlen($s) - 9 - 6));
  }
  
}//class

class tstorage extends tfilestorage {
  private static $data;
  private static $modified;
  
  public static function save(tdata $obj) {
    self::$modified = true;
    $base = $obj->getbasename();
    if (!isset(self::$data[$base])) self::$data[$base] = &$obj->data;
    return true;
  }
  
  public static function load(tdata $obj) {
    $base = $obj->getbasename();
    if (isset(self::$data[$base])) {
      $obj->data = &self::$data[$base];
      $obj->afterload();
      return true;
    } else {
      self::$data[$base] = &$obj->data;
      return false;
    }
  }
  
  public static function savemodified() {
    if (self::$modified) {
      if (self::$disabled) return false;
      $lock = litepublisher::$paths->data .'storage.lok';
      if (($fh = @fopen($lock, 'w')) &&       flock($fh, LOCK_EX | LOCK_NB)) {
        self::savetofile(litepublisher::$paths->data .'storage', self::comment_php(serialize(self::$data)));
        flock($fh, LOCK_UN);
        fclose($fh);
        @chmod($lock, 0666);
      } else {
        tfiler::log('Storage locked, data not saved');
      }
      self::$modified = false;
      return true;
    }
    return false;
  }
  
  public static function loaddata() {
    self::$data = array();
    $filename = litepublisher::$paths->data . 'storage.php';
    if (file_exists($filename)) {
      $s = self::uncomment_php(file_get_contents($filename));
      if (!empty($s)) self::$data = unserialize($s);
      return true;
    }
    return false;
  }
  
}//class

class tarray2prop {
  public $array;
public function __construct(array $a = null) { $this->array = $a; }
public function __destruct() { unset($this->array); }
public function __get($name) { return $this->array[$name]; }
public function __set($name, $value) { $this->array[$name] = $value; }
public function __isset($name) { return array_key_exists($name, $this->array); }
public function __tostring() { return $this->array['']; }
}//class

function sqldate($date = 0) {
  if ($date == 0) $date = time();
  return date('Y-m-d H:i:s', $date);
}

function dbquote($s) {
  return litepublisher::$db->quote($s);
}

function md5uniq() {
  return md5(mt_rand() . litepublisher::$secret. microtime());
}

function strbegin($s, $begin) {
  return strncmp($s, $begin, strlen($begin)) == 0;
}

function strbegins() {
  $a = func_get_args();
  $s = array_shift($a);
  while ($begin = array_shift($a)) {
    if (strncmp($s, $begin, strlen($begin)) == 0) return true;
  }
  return false;
}

function strend($s, $end) {
  return $end == substr($s, 0 - strlen($end));
}

function array_delete(array &$a, $i) {
  array_splice($a, $i, 1);
}

function array_delete_value(array &$a, $value) {
  $i = array_search($value, $a);
  if ($i !== false)         array_splice($a, $i, 1);
}

function array_insert(array &$a, $item, $index) {
  array_splice($a, $index, 0, array($item));
}

function array_move(array &$a, $oldindex, $newindex) {
  //delete and insert
  if (($oldindex == $newindex) || !isset($a[$oldindex])) return false;
  $item = $a[$oldindex];
  array_splice($a, $oldindex, 1);
  array_splice($a, $newindex, 0, array($item));
}

function dumpstr($s) {
  echo "<pre>\n" . htmlspecialchars($s) . "</pre>\n";
}

//events.class.php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class ECancelEvent extends Exception {
  public $result;
  
  public function __construct($message, $code = 0) {
    $this->result = $message;
    parent::__construct('', 0);
  }
}

class tevents extends tdata {
  protected $events;
  protected $eventnames;
  protected $map;
  
  public function __construct() {
    $this->eventnames = array();
    $this->map = array();
    parent::__construct();
    $this->assignmap();
    $this->load();
  }
  
  public function __destruct() {
    unset($this->data, $this->events, $this->eventnames, $this->map);
  }
  
  protected function create() {
    $this->addmap('events', array());
    $this->addmap('coclasses', array());
  }
  
  public function assignmap() {
    foreach ($this->map as $propname => $key) {
      $this->$propname = &$this->data[$key];
    }
  }
  
  public function afterload() {
    $this->assignmap();
    foreach ($this->coclasses as $coclass) {
      $this->coinstances[] = getinstance($coclass);
    }
    parent::afterload();
  }
  
  protected function addmap($name, $value) {
    $this->map[$name] = $name;
    $this->data[$name] = $value;
    $this->$name = &$this->data[$name];
  }
  
  public function free() {
    unset(litepublisher::$classes->instances[get_class($this)]);
    foreach ($this->coinstances as $coinstance) $coinstance->free();
  }
  
  public function eventexists($name) {
    return in_array($name, $this->eventnames);
  }
  
  public function __get($name) {
    if (method_exists($this, $name)) return array('class' =>get_class($this), 'func' => $name);
    return parent::__get($name);
  }
  
  public function __set($name, $value) {
    if (parent::__set($name, $value)) return true;
    if (in_array($name, $this->eventnames)) {
      $this->addevent($name, $value['class'], $value['func']);
      return true;
    }
    $this->error("Unknown property $name in class ". get_class($this));
  }
  
  public function method_exists($name) {
    return in_array($name, $this->eventnames);
  }
  
  public  function __call($name, $params) {
    if (in_array($name, $this->eventnames)) return $this->callevent($name, $params);
    parent::__call($name, $params);
  }
  
  public function __isset($name) {
    if (parent::__isset($name)) return true;
    return in_array($name, $this->eventnames);
  }
  
  protected function addevents() {
    $a = func_get_args();
    array_splice($this->eventnames, count($this->eventnames), 0, $a);
  }
  
  private function get_events($name) {
    if (isset($this->events[$name])) return $this->events[$name];
    return false;
  }
  
  public function callevent($name, $params) {
    $result = '';
    if (    $list = $this->get_events($name)) {
      
      foreach ($list as $i => $item) {
        if (empty($item['class'])) {
          if (function_exists($item['func'])) {
            $call = $item['func'];
          } else {
            $this->delete_event_item($name, $i);
            continue;
          }
        } elseif (!class_exists($item['class'])) {
          $this->delete_event_item($name, $i);
          continue;
        } else {
          $obj = getinstance($item['class']);
          $call = array($obj, $item['func']);
        }
        try {
          $result = call_user_func_array($call, $params);
        } catch (ECancelEvent $e) {
          return $e->result;
        }
      }
    }
    
    return $result;
  }
  
  public static function cancelevent($result) {
    throw new ECancelEvent($result);
  }
  
  private function delete_event_item($name, $i) {
    array_splice($this->events[$name], $i, 1);
    if (count($this->events[$name]) == 0) unset($this->events[$name]);
    $this->save();
  }
  
  public function setevent($name, $params) {
    return $this->addevent($name, $params['class'], $params['func']);
  }
  
  public function addevent($name, $class, $func) {
    if (!in_array($name, $this->eventnames)) return $this->error(sprintf('No such %s event', $name ));
    if (empty($func)) return false;
    if (isset($this->events[$name])) {
      if ($list = $this->get_events($name)) {
        foreach ($list  as $event) {
          if (($event['class'] == $class) && ($event['func'] == $func)) return false;
        }
      }
    } else {
      $this->events[$name] =array();
    }
    
    $this->events[$name][] = array(
    'class' => $class,
    'func' => $func
    );
    $this->save();
  }
  
  public function delete_event_class($name, $class) {
    if (    $list = $this->get_events($name)) {
      foreach ($list  as $i => $item) {
        if ($item['class'] == $class) {
          $this->delete_event_item($name, $i);
          return true;
        }
      }
    }
    return false;
  }
  
  public function unsubscribeclass($obj) {
    $this->unsubscribeclassname(get_class($obj));
  }
  
  public function unsubscribeclassname($class) {
    foreach ($this->events as $name => $events) {
      foreach ($events as $i => $item) {
        if ($item['class'] == $class) array_splice($this->events[$name], $i, 1);
      }
    }
    
    $this->save();
  }
  
  public function seteventorder($eventname, $instance, $order) {
    if (!isset($this->events[$eventname])) return false;
    $class = get_class($instance);
    $count = count($this->events[$eventname]);
    if (($order < 0) || ($order >= $count)) $order = $count - 1;
    foreach ($this->events[$eventname] as $i => $event) {
      if ($class == $event['class']) {
        if ($i == $order) return true;
        array_splice($this->events[$eventname], $i, 1);
        array_splice($this->events[$eventname], $order, 0, array(0 => $event));
        $this->save();
        return true;
      }
    }
  }
  
  private function indexofcoclass($class) {
    return array_search($class, $this->coclasses);
  }
  
  public function addcoclass($class) {
    if ($this->indexofcoclass($class) === false) {
      $this->coclasses[] = $class;
      $this->save();
      $this->coinstances = getinstance($class);
    }
  }
  
  public function deletecoclass($class) {
    $i = $this->indexofcoclass($class);
    if (is_int($i)) {
      array_splice($this->coclasses, $i, 1);
      $this->save();
    }
  }
  
}//class

class tevents_storage extends tevents {
  
  public function load() {
    return tstorage::load($this);
  }
  
  public function save() {
    return tstorage::save($this);
  }
  
}//class


class tcoevents extends tevents {
  private $owner;
  
  public function __construct() {
    parent::__construct();
    $a = func_get_args();
    $owner = array_shift ($a);
    $this->owner = $owner;
    if (!isset($owner->data['events'])) $owner->data['events'] = array();
    $this->events = &$owner->data['events'];
    array_splice($this->eventnames, count($this->eventnames), 0, $a);
  }
  
  public function __destruct() {
    parent::__destruct();
    unset($this->owner);
  }
  
public function assignmap() {}
protected function create() { }
public function load() {}
  public function afterload() {
    $this->events = &$this->owner->data['events'];
  }
  
  public function save() {
    return $this->owner->save();
  }
  
  
}//class

//items.class.php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class titems extends tevents {
  public $items;
  protected $autoid;
  public $dbversion;
  
  protected function create() {
    parent::create();
    $this->addevents('added', 'deleted');
    if ($this->dbversion) {
      $this->items = array();
    } else {
      $this->addmap('items', array());
      $this->addmap('autoid', 0);
    }
  }
  
  public function load() {
    if ($this->dbversion) {
      return tstorage::load($this);
    } else {
      return parent::load();
    }
  }
  
  public function save() {
    if ($this->dbversion) {
      return tstorage::save($this);
    } else {
      return parent::save();
    }
  }
  
  public function loadall() {
    if (!$this->dbversion)  return;
    return $this->select('', '');
  }
  
  public function loaditems(array $items) {
    if (!$this->dbversion) return;
    //исключить из загрузки загруженные посты
    $items = array_diff($items, array_keys($this->items));
    if (count($items) == 0) return;
    $list = implode(',', $items);
    $this->select("$this->thistable.id in ($list)", '');
  }
  
  public function select($where, $limit) {
    if (!$this->dbversion) $this->error('Select method must be called ffrom database version');
    if ($where != '') $where = 'where '. $where;
    return $this->res2items($this->db->query("SELECT * FROM $this->thistable $where $limit"));
  }
  
  public function res2items($res) {
    if (!$res) return array();
    $result = array();
    while ($item = litepublisher::$db->fetchassoc($res)) {
      $id = $item['id'];
      $result[] = $id;
      $this->items[$id] = $item;
    }
    return $result;
  }
  
  public function getcount() {
    if ($this->dbversion) {
      return $this->db->getcount();
    } else {
      return count($this->items);
    }
  }
  
  public function getitem($id) {
    if (isset($this->items[$id])) return $this->items[$id];
    if ($this->dbversion) {
      if ($this->select("$this->thistable.id = $id", 'limit 1')) return $this->items[$id];
    }
    return $this->error("Item $id not found in class ". get_class($this));
  }
  
  public function getvalue($id, $name) {
    if ($this->dbversion && !isset($this->items[$id])) $this->items[$id] = $this->db->getitem($id);
    return $this->items[$id][$name];
  }
  
  public function setvalue($id, $name, $value) {
    $this->items[$id][$name] = $value;
    if ($this->dbversion) {
      $this->db->setvalue($id, $name, $value);
    }
  }
  
  public function itemexists($id) {
    if (isset($this->items[$id])) return true;
    if ($this->dbversion) {
      try {
        return $this->getitem($id);
      } catch (Exception $e) {
        return false;
      }
    }
    return false;
  }
  
  public function IndexOf($name, $value) {
    if ($this->dbversion){
      return $this->db->findid("$name = ". dbquote($value));
    }
    
    foreach ($this->items as $id => $item) {
      if ($item[$name] == $value) {
        return $id;
      }
    }
    return false;
  }
  
  public function additem(array $item) {
    $id = $this->dbversion ? $this->db->add($item) : ++$this->autoid;
    $item['id'] = $id;
    $this->items[$id] = $item;
    if (!$this->dbversion) $this->save();
    $this->added($id);
    return $id;
  }
  
  public function delete($id) {
    if ($this->dbversion) $this->db->delete("id = $id");
    if (isset($this->items[$id])) {
      unset($this->items[$id]);
      if (!$this->dbversion) $this->save();
      $this->deleted($id);
      return true;
    }
    return false;
  }
  
}//class

class titems_storage extends titems {
  
  public function load() {
    return tstorage::load($this);
  }
  
  public function save() {
    return tstorage::save($this);
  }
  
}//class

class tsingleitems extends titems {
  public static $instances;
  public $id;
  
  public static function singleinstance($class, $id = 0) {
    if (!isset(self::$instances)) self::$instances = array();
    if (isset(self::$instances[$class][$id]))     return self::$instances[$class][$id];
    $self = litepublisher::$classes->newinstance($class);
    self::$instances[$class][$id] = $self;
    $self->id = $id;
    $self->load();
    return $self;
  }
  
  public function load() {
    if (!isset($this->id)) return false;
    return parent::load();
  }
  
  public function free() {
    unset(self::$instances[get_class($this)][$this->id]);
  }
  
}//class

//classes.class.php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

if (!function_exists( 'spl_autoload_register' ) ) {
  function __autoload($class) {
    litepublisher::$classes->_autoload($class);
  }
}

class tclasses extends titems {
  public $classes;
  public $interfaces;
  public $remap;
  public $instances;
  
  public static function instance() {
    if (!isset(litepublisher::$classes)) {
      $class = __class__;
      litepublisher::$classes = new $class();
      litepublisher::$classes->instances[$class] = litepublisher::$classes;
    }
    return litepublisher::$classes;
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'classes';
    $this->dbversion = false;
    $this->addevents('onnewitem', 'gettemplatevar');
    $this->addmap('classes', array());
    $this->addmap('interfaces', array());
    $this->addmap('remap', array());
    $this->instances = array();
    if (function_exists('spl_autoload_register')) spl_autoload_register(array(&$this, '_autoload'));
  }
  
  public function load() {
    return tstorage::load($this);
  }
  
  public function save() {
    return tstorage::save($this);
  }
  
  public function getinstance($class) {
    if (!class_exists($class)) {
      $this->error("Class $class not found");
    }
    if (!isset($this->instances[$class])) {
      $this->instances[$class] = $this->newinstance($class);
    }
    return $this->instances[$class];
  }
  
  public function newinstance($class) {
    if (!empty($this->remap[$class])) $class = $this->remap[$class];
    return new $class();
  }
  
  public function newitem($name, $class, $id) {
    //echo"$name:$class:$id new<br>\n";
    if (!empty($this->remap[$class])) $class = $this->remap[$class];
    $this->callevent('onnewitem', array($name, &$class, $id));
    return new $class();
  }
  
  public function __get($name) {
    if (isset($this->classes[$name])) return $this->getinstance($this->classes[$name]);
    $class = 't' . $name;
    if (isset($this->items[$class])) return $this->getinstance($class);
    return parent::__get($name);
  }
  
  public function add($class, $filename, $path = '') {
    if (!isset($this->items[$class]) ||
    ($this->items[$class][0] != $filename) || ($this->items[$class][1] != $path)) {
      $this->items[$class] = array($filename, $path);
      //$this->save();
      $instance = $this->getinstance($class);
      if (method_exists($instance, 'install')) $instance->install();
    }
    $this->save();
    $this->added($class);
  }
  
  public function delete($class) {
    if (isset($this->items[$class])) {
      if (class_exists($class)) {
        $instance = $this->getinstance($class);
        if (method_exists($instance, 'uninstall')) $instance->uninstall();
      }
      unset($this->items[$class]);
      $this->save();
      $this->deleted($class);
    }
  }
  
  public function reinstall($class) {
    if (isset($this->items[$class])) {
      $this->lock();
      $item = $this->items[$class];
      $this->delete($class);
      $this->add($class, $item[0], $item[1]);
      $this->unlock();
    }
  }
  
  public function _autoload($class) {
    if ($filename = $this->getclassfilename($class)) {
      if (file_exists($filename)) require_once($filename);
    }
    return false;
  }
  
  public function getclassfilename($class) {
    if ($path =$this->getpath($class))  return $path . $this->items[$class][0];
    if (isset($this->interfaces[$class])) return litepublisher::$paths->lib . $this->interfaces[$class];
    return false;
  }
  
  public function getpath($class) {
    if (!isset($this->items[$class])) return false;
    if (empty($this->items[$class][1])) return litepublisher::$paths->lib;
    $result = trim($this->items[$class][1], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    $filename = $result . $this->items[$class][0];
    if (file_exists($filename))  return $result;
    //may be is subdir?
    if (file_exists(litepublisher::$paths->plugins . $filename)) return litepublisher::$paths->plugins . $result;
    if (file_exists(litepublisher::$paths->themes . $filename)) return litepublisher::$paths->themes . $result;
    if  (file_exists(litepublisher::$paths->home . $filename)) return  litepublisher::$paths->home . $result;
    return false;
  }
  
  
  public function exists($class) {
    return isset($this->items[$class]);
  }
  
}//class

function getinstance($class) {
  return litepublisher::$classes->getinstance($class);
}

//options.class.php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class toptions extends tevents_storage {
  public $user;
  public $group;
  public $admincookie;
  public $gmt;
  public $errorlog;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'options';
    $this->addevents('changed', 'perpagechanged', 'onsave');
    unset($this->cache);
    $this->gmt = 0;
    $this->errorlog = '';
    $this->admincookie = false;
  }
  
  public function afterload() {
    parent::afterload();
    date_default_timezone_set($this->timezone);
    $this->gmt = date('Z');
    if (!defined('dbversion')) {
      define('dbversion', isset($this->data['dbconfig']));
    }
  }
  
  public function savemodified() {
    $result = tstorage::savemodified();
    $this->onsave($result);
    return $result;
  }
  
  public function __set($name, $value) {
    if (in_array($name, $this->eventnames)) {
      $this->addevent($name, $value['class'], $value['func']);
      return true;
    }
    
    if (method_exists($this, $set = 'set' . $name)) {
      $this->$set($value);
      return true;
    }
    
    if (!array_key_exists($name, $this->data)  || ($this->data[$name] != $value)) {
      $this->data[$name] = $value;
      $this->save();
      $this->dochanged($name, $value);
    }
    return true;
  }
  
  private function dochanged($name, $value) {
    if ($name == 'perpage') {
      $this->perpagechanged();
      $urlmap = turlmap::instance();
      $urlmap->clearcache();
    } elseif ($name == 'cache') {
      $urlmap = turlmap::instance();
      $urlmap->clearcache();
    } else {
      $this->changed($name, $value);
    }
  }
  
  public function delete($name) {
    if (array_key_exists($name, $this->data)) {
      unset($this->data);
      $this->save();
    }
  }
  
  public function authcookie() {
    if (empty($_COOKIE['admin']))  return false;
    $cookie = md5((string) $_COOKIE['admin'] . litepublisher::$secret);
    if ($this->cookie == $cookie) {
      if ($this->cookieexpired < time()) return false;
      $this->user = 1;
    } elseif (!$this->usersenabled)  {
      return false;
    } else {
      $users = tusers::instance();
      if (!($this->user = $users->authcookie($_COOKIE['admin']))) return false;
    }
    
    $this->updategroup();
    return true;
  }
  
  public function auth($login, $password) {
    if ($login == '' && $password == '' && $this->cookieenabled) return $this->authcookie();
    if ($login == $this->login) {
      if ($this->password != md5("$login:$this->realm:$password"))  return false;
      $this->user = 1;
    } elseif(!$this->usersenabled) {
      return false;
    } else {
      $users = tusers::instance();
      if (!($this->user = $users->auth($login, $password))) return false;
    }
    $this->updategroup();
    return true;
  }
  
  public function updategroup() {
    if ($this->user == 1) {
      $this->group = 'admin';
    } else {
      $users = tusers::instance();
      $this->group = $users->getgroupname($this->user);
    }
  }
  
  public function getpassword() {
    if ($this->user <= 1) return $this->data['password'];
    $users = tusers::instance();
    return $users->getvalue($this->user, 'password');
  }
  
  public function changepassword($newpassword) {
    $this->password = md5("$this->login:$this->realm:$newpassword");
  }
  
  public function setdbpassword($password) {
    $this->data['dbconfig']['password'] = base64_encode(str_rot13 ($password));
    $this->save();
  }
  
  public function Getinstalled() {
    return isset($this->data['login']);
  }
  
  public function settimezone($value) {
    if(!isset($this->data['timezone']) || ($this->timezone != $value)) {
      $this->data['timezone'] = $value;
      $this->save();
      date_default_timezone_set($this->timezone);
      $this->gmt = date('Z');
    }
  }
  
  public function setcookie($cookie) {
    if ($cookie != '') $cookie = md5((string) $cookie . litepublisher::$secret);
    $this->data['cookie'] = $cookie;
    $this->save();
  }
  
  public function getcommentsapproved() {
    return $this->DefaultCommentStatus  == 'approved';
  }
  
  public function setcommentsapproved($value) {
    $this->DefaultCommentStatus  = $value ? 'approved' : 'hold';
  }
  
  public function handexception($e) {
    /*
    echo "<pre>\n";
    $debug = debug_backtrace();
    foreach ($debug as $error) {
      echo $error['function'] ;
      echo "\n";
    }
    //array_shift($debug);
    echo "</pre>\n";
    */
    $trace =str_replace(litepublisher::$paths->home, '', $e->getTraceAsString());
    
    $message = "Caught exception:\n" . $e->getMessage();
    $log = $message . "\n" . $trace;
    $this->errorlog .= str_replace("\n", "<br />\n", htmlspecialchars($log));
    tfiler::log($log, 'exceptions.log');
    $urlmap = turlmap::instance();
    if (!(litepublisher::$debug || $this->echoexception || $this->admincookie || $urlmap->adminpanel)) {
      tfiler::log($log, 'exceptionsmail.log');
    }
  }
  
  public function trace($msg) {
    try {
      throw new Exception($msg);
    } catch (Exception $e) {
      $this->handexception($e);
    }
  }
  
  public function showerrors() {
    if (!empty($this->errorlog) && (litepublisher::$debug || $this->echoexception || $this->admincookie || litepublisher::$urlmap->adminpanel)) {
      echo $this->errorlog;
    }
  }
  
}//class

//site.class.php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tsite extends tevents_storage {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'site';
  }
  
  public function __set($name, $value) {
    if (in_array($name, $this->eventnames)) {
      $this->addevent($name, $value['class'], $value['func']);
      return true;
    }
    
    if (!array_key_exists($name, $this->data)  || ($this->data[$name] != $value)) {
      $this->data[$name] = $value;
      $this->save();
    }
    return true;
  }
  
  public function geturl() {
    if ($this->fixedurl) return $this->data['url'];
    return 'http://'. litepublisher::$domain;
  }
  
  public function seturl($url) {
    $url = rtrim($url, '/');
    $this->data['url'] = $url;
    $this->files= $url;
    $this->subdir = '';
    if ($i = strpos($url, '/', 10)) {
      $this->subdir = substr($url, $i);
    }
    $this->save();
  }
  
  public function getversion() {
    return litepublisher::$options->data['version'];
  }
  
  public function getlanguage() {
    return litepublisher::$options->data['language'];
  }
  
}//class

//urlmap.class.php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class turlmap extends titems {
  public $host;
  public $url;
  public $page;
  public $uripath;
  public $itemrequested;
  public  $context;
  public $cachefilename;
  public $argtree;
  public $is404;
  public $adminpanel;
  public $mobile;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = dbversion;
    parent::create();
    $this->table = 'urlmap';
    $this->basename = 'urlmap';
    $this->addevents('beforerequest', 'afterrequest', 'onclearcache');
    $this->is404 = false;
    $this->adminpanel = false;
    $this->mobile= false;
    $this->cachefilename = false;
    $this->page = 1;
  }
  
  protected function prepareurl($host, $url) {
    $this->host = $host;
    $this->page = 1;
    $this->uripath = array();
    if (litepublisher::$site->q == '?') {
      $this->url = substr($url, strlen(litepublisher::$site->subdir));
    } else {
      $this->url = $_GET['url'];
    }
  }
  
  public function request($host, $url) {
    $this->prepareurl($host, $url);
    $this->adminpanel = strbegin($this->url, '/admin/') || ($this->url == '/admin');
    $this->beforerequest();
    if (!litepublisher::$debug && litepublisher::$options->ob_cache) ob_start();
    try {
      $this->dorequest($this->url);
    } catch (Exception $e) {
      litepublisher::$options->handexception($e);
    }
    if (!litepublisher::$debug && litepublisher::$options->ob_cache) @ob_end_flush ();
    $this->afterrequest($this->url);
    $this->CheckSingleCron();
  }
  
  private function dorequest($url) {
    if ($this->itemrequested = $this->finditem($url)){
      return $this->printcontent($this->itemrequested);
    } else {
      $this->notfound404();
    }
  }
  
  private function query($url) {
    if (dbversion) {
      if ($item = $this->db->getassoc('url = '. dbquote($url). ' limit 1')) {
        $this->items[$item['id']] = $item;
        return $item;
      }
    } elseif (isset($this->items[$url])) {
      return $this->items[$url];
    }
    return false;
  }
  
  public function finditem($url) {
    if ($i = strpos($url, '?'))  {
      $url = substr($url, 0, $i);
    }
    
    if ('//' == substr($url, -2)) $this->redir301(rtrim($url, '/') . '/');
    
    //extract page number
    if (preg_match('/(.*?)\/page\/(\d*?)\/+$/', $url, $m)) {
      if ('/' != substr($url, -1))  return $this->redir301($url . '/');
      $url = $m[1];
      if ($url == '') $url = '/';
      $this->page = (int) $m[2];
    }
    
    if ($result = $this->query($url)) return $result;
    $url = $url != rtrim($url, '/') ? rtrim($url, '/') : $url . '/';
    if ($result = $this->query($url)) {
      if ($this->page > 1) return $result;
      if ($result['type'] == 'normal') return $this->redir301($url);
      return $result;
    }
    
    $this->uripath = explode('/', trim($url, '/'));
    //tree convert into argument
    $url = trim($url, '/');
    $j = -1;
    while($i = strrpos($url, '/', $j)) {
      if ($result = $this->query('/' . substr($url, 0, $i + 1))) {
        if ($result['type'] != 'tree') return false;
        $this->argtree = substr($url, $i +1);
        return $result;
      }
      $j = - (strlen($url) - $i + 1);
    }
    
    return false;
  }
  
  public function findurl($url) {
    if (dbversion) {
      if ($result = $this->db->finditem('url = '. dbquote($url))) return $result;
    } else {
      if (isset($this->items[$url])) return $this->items[$url];
    }
    return false;
  }
  
  private function getcachefile(array $item) {
    if (!$this->cachefilename) {
      if ($item['type'] == 'normal') {
        $this->cachefilename =  sprintf('%s-%d.php', $item['id'], $this->page);
      } else {
        $this->cachefilename = sprintf('%s-%d-%s.php', $item['id'], $this->page, md5($this->url));
      }
    }
    return litepublisher::$paths->cache . $this->cachefilename;
  }
  
  private function  printcontent(array $item) {
    $options = litepublisher::$options;
    if ($options->cache && !$options->admincookie) {
      $cachefile = $this->getcachefile($item);
      if (file_exists($cachefile) && ((filemtime ($cachefile) + $options->expiredcache - $options->filetime_offset) >= time())) {
        include($cachefile);
        return;
      }
    }
    
    if (class_exists($item['class']))  {
      return $this->GenerateHTML($item);
    } else {
      //$this->deleteclass($item['class']);
      $this->notfound404();
    }
  }
  
  public function getidcontext($id) {
    if ($this->dbversion) {
      $item = $this->getitem($id);
    } else {
      foreach ($this->items as $url => $item) {
        if ($id == $item['id']) break;
      }
    }
    return $this->getcontext($item);
  }
  
  public function getcontext(array $item) {
    $class = $item['class'];
    $parents = class_parents($class);
    if (in_array('titem', $parents)) {
      return call_user_func_array(array($class, 'instance'), array($item['arg']));
    } else {
      return getinstance($class);
    }
  }
  
  protected function GenerateHTML(array $item) {
    $this->context = $this->getcontext($item);
    //special handling for rss
    if (method_exists($this->context, 'request') && ($s = $this->context->request($item['arg']))) {
      //tfiler::log("$this->url\n$s");
      switch ($s) {
        case 404: return $this->notfound404();
        case 403: return $this->forbidden();
      }
    } else {
      $template = ttemplate::instance();
      $s = $template->request($this->context);
    }
    eval('?>'. $s);
    if (litepublisher::$options->cache && $this->context->cache &&!litepublisher::$options->admincookie) {
      $cachefile = $this->getcachefile($item);
      file_put_contents($cachefile, $s);
      chmod($cachefile, 0666);
    }
  }
  
  public function notfound404() {
    $redir = tredirector::instance();
    if ($url  = $redir->get($this->url)) {
      return $this->redir301($url);
    }
    
    $this->is404 = true;
    $obj = tnotfound404::instance();
    $Template = ttemplate::instance();
    $s = $Template->request($obj);
    eval('?>'. $s);
  }
  
  public function forbidden() {
    $this->is404 = true;
    $obj = tforbidden::instance();
    $Template = ttemplate::instance();
    $s = $Template->request($obj);
    eval('?>'. $s);
  }
  
  public function urlexists($url) {
    if (dbversion) {
      return $this->db->findid('url = '. dbquote($url));
    } else {
      return isset($this->items[$url]) ? $this->items[$url]['id'] : false;
    }
  }
  
  public function addget($url, $class) {
    return $this->add($url, $class, null, 'get');
  }
  
  public function add($url, $class, $arg, $type = 'normal') {
    if (!in_array($type, array('normal','get','tree'))) $this->error(sprintf('Invalid url type %s', $type));
    if (dbversion) {
      if ($item = $this->db->finditem('url = ' . dbquote($url))) $this->error(sprintf('Url "%s" already exists', $url));
      $item= array(
      'url' => $url,
      'class' => $class,
      'arg' => $arg,
      'type' => $type
      );
      $item['id'] = $this->db->add($item);
      $this->items[$item['id']] = $item;
      return $item['id'];
    }
    
    if (isset($this->items[$url])) $this->error(sprintf('Url "%s" already exists', $url));
    $this->items[$url] = array(
    'id' => ++$this->autoid,
    'class' => $class,
    'arg' => $arg,
    'type' => $type
    );
    $this->save();
    return $this->autoid;
  }
  
  public function delete($url) {
    if (dbversion) {
      $url = dbquote($url);
      if ($id = $this->db->findid('url = ' . $url)) {
        $this->db->iddelete($id);
      } else {
        return false;
      }
    } elseif (isset($this->items[$url])) {
      $id = $this->items[$url]['id'];
      unset($this->items[$url]);
      $this->save();
    } else {
      return false;
    }
    $this->clearcache();
    $this->deleted($id);
    return true;
  }
  
  public function deleteclass($class) {
    if (dbversion){
      if ($items =
      $this->db->getitems("class = '$class'")) {
        $this->db->delete("class = '$class'");
        foreach ($items as $item) $this->deleted($item);
      }
    } else  {
      foreach ($this->items as $url => $item) {
        if ($item['class'] == $class) {
          $item = $this->items[$url];
          unset($this->items[$url]);
          $this->deleted($item);
        }
      }
      $this->save();
    }
    $this->clearcache();
  }
  
  public function deleteitem($id) {
    if (dbversion){
      if ($item = $this->db->getitem($id)) {
        $this->db->iddelete($id);
        $this->deleted($item);
      }
    } else  {
      foreach ($this->items as $url => $item) {
        if ($item['id'] == $id) {
          $item = $this->items[$url];
          unset($this->items[$url]);
          $this->save();
          $this->deleted($item);
          break;
        }
      }
    }
    $this->clearcache();
  }
  
  //for Archives
  public function GetClassUrls($class) {
    if (dbversion) {
      $res = $this->db->query("select url from $this->thistable where class = '$class'");
      return $this->db->res2id($res);
    }
    
    $result = array();
    foreach ($this->items as $url => $item) {
      if ($item['class'] == $class) $result[] = $url;
    }
    return $result;
  }
  
  public function clearcache() {
    $path = litepublisher::$paths->cache;
    if ( $h = @opendir($path)) {
      while(FALSE !== ($filename = @readdir($h))) {
        if (($filename == '.') || ($filename == '..') || ($filename == '.svn')) continue;
        $file = $path. $filename;
        if (is_dir($file)) {
          tfiler::delete($file . DIRECTORY_SEPARATOR, true, true);
        } else {
          unlink($file);
        }
      }
      closedir($h);
    }
    
    $this->onclearcache();
  }
  
  public function setexpired($id) {
    tfiler::deletemask(litepublisher::$paths->cache . "$id-*.php");
  }
  
  public function setexpiredcurrent() {
    $filename = $this->getcachefile($this->itemrequested);
    if (file_exists($filename)) unlink($filename);
  }
  
  public function getcachename($name, $id) {
    return litepublisher::$paths->cache. "$name-$id.php";
  }
  
  public function expiredname($name, $id) {
    tfiler::deletedirmask(litepublisher::$paths->cache, "*$name-$id.php");
  }
  
  public function expiredclass($class) {
    if (dbversion) {
      $items = $this->db->idselect("class = '$class'");
      foreach ($items as $id) $this->setexpired($id);
    } else {
      foreach ($this->items as $url => $item) {
        if ($class == $item['class']) $this->setexpired($item['id']);
      }
    }
  }
  
  public function addredir($from, $to) {
    if ($from == $to) return;
    $Redir = &tredirector::instance();
    $Redir->add($from, $to);
  }
  
  public static function unsub($obj) {
    $self = self::instance();
    $self->lock();
    $self->unsubscribeclassname(get_class($obj));
    $self->deleteclass(get_class($obj));
    $self->unlock();
  }
  
  protected function CheckSingleCron() {
    if (defined('cronpinged')) return;
    /*
    $cronfile =litepublisher::$paths->data . 'cron' . DIRECTORY_SEPARATOR.  'crontime.txt';
    $time = file_exists($cronfile) ? filemtime($cronfile) : 0;
    if ($time + 3600 - litepublisher::$options->filetime_offset < time()) {
      register_shutdown_function('tcron::selfping');
    }
    */
    if (time() > litepublisher::$options->crontime + 3600) {
      litepublisher::$options->crontime = time();
      register_shutdown_function(array('tcron', 'selfping'));
    }
  }
  
  public static function redir301($to) {
    self::redir(litepublisher::$site->url . $to);
  }
  
  public static function redir($url) {
    litepublisher::$options->savemodified();
    if ( php_sapi_name() != 'cgi-fcgi' ) {
      $protocol = $_SERVER["SERVER_PROTOCOL"];
      if ( ('HTTP/1.1' != $protocol) && ('HTTP/1.0' != $protocol) ) $protocol = 'HTTP/1.0';
      header( "$protocol 301 Moved Permanently", true, 301);
    }
    
    header("Location: $url");
    if (ob_get_level()) ob_end_flush ();
    exit();
  }
  
  //db
  public function getidurl($id) {
    if (dbversion) {
      if (!isset($this->items[$id])) {
        $this->items[$id] = $this->db->getitem($id);
      }
      return $this->items[$id]['url'];
    } else {
      foreach ($this->items as $url => $item) {
        if ($item['id'] == $id) return $url;
      }
    }
  }
  
  public function setidurl($id, $url) {
    if (dbversion) {
      $this->db->setvalue($id, 'url', $url);
      if (isset($this->items[$id])) $this->items[$id]['url'] = $url;
    } else {
      foreach ($this->items as $u => $item) {
        if ($id == $item['id']) {
          unset($this->items[$u]);
          $this->items[$url] = $item;
          $this->save();
          return;
        }
      }
    }
  }
  
  public function getnextpage() {
    $url = $this->itemrequested['url'];
    return litepublisher::$site->url . rtrim($url, '/') . '/page/' . ($this->page + 1) . '/';
  }
  
  public function getprevpage() {
    $url = $this->itemrequested['url'];
    if ($this->page <= 2) return url;
    return litepublisher::$site->url . rtrim($url, '/') . '/page/' . ($this->page - 1) . '/';
  }
  
  public static function htmlheader($cache) {
    return sprintf('<?php turlmap::sendheader(%s); ?>', $cache ? 'true' : 'false');
  }
  
  public static function sendheader($cache) {
    if (!$cache) {
      Header( 'Cache-Control: no-cache, must-revalidate');
      Header( 'Pragma: no-cache');
    }
    header('Content-Type: text/html; charset=utf-8');
    header('Last-Modified: ' . date('r'));
    header('X-Pingback: " . litepublisher::$site->url . "/rpc.xml');
  }
  
  public static function sendxml() {
    header('Content-Type: text/xml; charset=utf-8');
    header('Last-Modified: ' . date('r'));
    header('X-Pingback: " . litepublisher::$site->url . "/rpc.xml');
    echo '<?xml version="1.0" encoding="utf-8" ?>';
  }
  
}//class

//interfaces.php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

interface itemplate {
  public function request($arg);
  public function gettitle();
  public function getkeywords();
  public function getdescription();
  public function gethead();
  public function getcont();
  public function getidview();
  public function setidview($id);
}

interface iwidgets {
  public function getwidgets(array &$items, $sidebar);
  public function getsidebar(&$content, $sidebar);
}

interface iposts {
  public function add(tpost $post);
  public function edit(tpost $post);
  public function delete($id);
}

//plugin.class.php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tplugin extends tevents {
  
  protected function create() {
    parent::create();
    $this->basename=  'plugins' .DIRECTORY_SEPARATOR  . strtolower(get_class($this));
  }
  
}

?>