<?php

//data.class.php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tdata {
  public $lockcount;
  public static $GlobalLock;
  public $data;
  public $basename;
  public $cache;
  //database
  public $table;
  
  public function __construct() {
    $this->lockcount = 0;
    $this->cache= true;
    $this->data= array();
    $this->basename = substr(get_class($this), 1);
    $this->create();
  }
  
  protected function create() {
  }
  
  public function __get($name) {
    if (method_exists($this, $get = "get$name"))  {
      return $this->$get();
    } elseif (array_key_exists($name, $this->data)) {
      return $this->data[$name];
    } else {
      return    $this->error("The requested property $name not found in class ". get_class($this));
    }
  }
  
  public function __set($name, $value) {
    if (method_exists($this, $set = "set$name")) {
      $this->$set($value);
      return true;
    }
    
    if (key_exists($name, $this->data)) {
      $this->data[$name] = $value;
      return true;
    }
    
    return false;
  }
  
  public  function __call($name, $params) {
    if (method_exists($this, strtolower($name))) {
      return call_user_func_array(array(&$this, strtolower($name)), $params);
    }
    $this->error("The requested method $name not found in class " . get_class($this));
  }
  
  public function propexists($name) {
    return array_key_exists($name, $this->data) || method_exists($this, "get$name") | method_exists($this, "Get$name") || isset($this->$name);
  }
  
  public function supported($interface) {
    return is_a($this, $interface);
  }
  
  public function error($Msg) {
    throw new Exception($Msg);
  }
  
  public function getbasename() {
    return $this->basename;
  }
  
  public function install() {
    $this->CallSatellite('install');
  }
  
  public function uninstall() {
    $this->CallSatellite('uninstall');
  }
  
  public function validate($repair = false) {
    $this->CallSatellite('validate', $repair);
  }
  
  protected function CallSatellite($func, $arg = null) {
    global $classes, $paths;
$func{0} = strtoupper($func{0});
    $parents = class_parents($this);
    array_splice($parents, 0, 0, get_class($this));
    foreach ($parents as $key => $class) {
      if ($path = $classes->getpath($class)) {
        $filename = basename($classes->items[$class][0], '.php') . '.install.php';
        $file =$path . 'install' . DIRECTORY_SEPARATOR . $filename;
        if (!@file_exists($file)) {
          $file =$path .  $filename;
          if (!@file_exists($file)) continue;
        }
        
        include_once($file);
        
        $fnc = $class . $func;
        if (function_exists($fnc)) $fnc($this, $arg);
      }
    }
  }
  
  public function load() {
    global $paths;
    if (dbversion == 'full') return $this->LoadFromDB();
    $filename = $paths['data'] . $this->getbasename() .'.php';
    if (@file_exists($filename)) {
      return $this->LoadFromString(PHPUncomment(file_get_contents($filename)));
    }
  }
  
  public function save() {
    global $paths;
    if (self::$GlobalLock || ($this->lockcount > 0)) return;
    if (dbversion == 'full') {
      $this->SaveToDB();
    } else {
      SafeSaveFile($paths['data'].$this->getbasename(), PHPComment($this->SaveToString()));
    }
  }
  
  public function SaveToString() {
    return serialize($this->data);
  }
  
  public function LoadFromString($s) {
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
  
  public function getdb($table = '') {
    global $db;
    $table =$table != '' ? $table : $this->table;
    if ($table != '') $db->table = $table;
    return $db;
  }
  
  protected function SaveToDB() {
    $db->add($this->getbasename(), $this->SaveToString());
  }
  
  protected function LoadFromDB() {
    if ($r = $this->db->select('basename = '. $this->getbasename() . "'")) {
      return $this->LoadFromString($r['data']);
    }
  }
  
  protected function getthistable() {
    global $db;
    return $db->prefix . $this->table;
  }
  
  protected function geturltable() {
    global $db;
    return $db->prefix .'urlmap';
  }
  
  protected function getjoinurl() {
    return " left join $this->urltable on $this->urltable.id = $this->thistable.idurl ";
  }
}//class

class tarray2prop {
  public $array;
public function __construct(array &$array) { $this->array = &$array; }
public function __get($name) { return $this->array[$name]; }
public function __set($name, $value) { $this->array[$name] = $value; }
public function __tostring() { return $this->array[0]; }
}//class

function sqldate($date = 0) {
  if ($date == 0) $date = time();
  return date('Y-m-d H:i:s', $date);
}

function dbquote($s) {
  global $db;
  return $db->quote($s);
}

function md5uniq() {
  return md5(mt_rand() . secret. microtime());
}

//events.class.php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

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
  
  public function free() {
    global $classes;
    unset($classes->instances[get_class($this)]);
  }
  
  protected function create() {
    $this->addmap('events', array());
  }
  
  public function assignmap() {
    foreach ($this->map as $propname => $key) {
      $this->$propname = &$this->data[$key];
    }
  }
  
  public function afterload() {
    $this->assignmap();
  }
  
  protected function addmap($name, $value) {
    $this->map[$name] = $name;
    $this->data[$name] = $value;
    $this->$name = &$this->data[$name];
  }
  
  public function __get($name) {
    if (method_exists($this, $name)) return array('class' =>get_class($this), 'func' => $name);
    return parent::__get($name);
  }
  
  public function __set($name, $value) {
    if (parent::__set($name, $value)) return true;
    if ($this->setevent($name, $value)) return true;
    $this->error("Unknown property $name in class ". get_class($this));
  }
  
  protected function setevent($name, $value) {
    if (in_array($name, $this->eventnames)) {
      $this->eventsubscribe($name, $value);
      return true;
    }
    return false;
  }
  
  public  function __call($name, $params) {
    if (in_array($name, $this->eventnames)) return $this->callevent($name, $params);
    parent::__call($name, $params);
  }
  
  protected function addevents() {
    $a = func_get_args();
    array_splice($this->eventnames, count($this->eventnames), 0, $a);
  }
  
  private function getevents($name) {
    if (isset($this->events[$name])) return $this->events[$name];
    return false;
  }
  
  private function callevent($name, &$params) {
    $result = '';
    if (    $list = $this->getevents($name)) {
      foreach ($list as $i => $item) {
        if (empty($item['class'])) {
          if (function_exists($item['func'])) {
            $call = $item['func'];
          } else {
            $this->eventdelete($name, $i);
            continue;
          }
        } elseif (!class_exists($item['class'])) {
          $this->eventdelete();
          continue;
        } else {
          $obj = getinstance($item['class']);
          $call = array(&$obj, $item['func']);
        }
        $result = call_user_func_array($call, $params);
      }
    }
    
    return $result;
  }
  
  private function eventdelete($name, $i) {
    array_splice($this->events[$name], $i, 1);
    $this->save();
  }
  
  public function eventsubscribe($name, $params) {
    if (!in_array($name, $this->eventnames)) return $this->error("No such $name event");
    if (!isset($this->events[$name])) $this->events[$name] =array();
    $list = $this->getevents($name);
    foreach ($list  as $event) {
      if (($event['class'] == $params['class']) && ($event['func'] == $params['func'])) return;
    }
    
    $this->events[$name][] = array(
    'class' => $params['class'],
    'func' => $params['func']
    );
    $this->save();
  }
  
  public function eventunsubscribe($name, $class) {
    if (    $list = $this->getevents($name)) {
      foreach ($list  as $i => $item) {
        if ($item['class'] == $class) {
          $this->eventdelete($name, $i);
          return true;
        }
      }
    }
    return false;
  }
  
  public static function unsub(&$obj) {
    $self = self::instance();
    $self->unsubscribeclassname(get_class($obj));
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
  
}//class

//items.class.php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class titems extends tevents {
  public $items;
  protected $autoid;
  protected $dbversion;
  
  protected function create() {
    parent::create();
    $this->dbversion = false;
    $this->addevents('added', 'deleted');
    $this->addmap('items', array());
    $this->addmap('autoid', 0);
  }
  
  public function load() {
    global $options;
    if ($this->dbversion) {
      if (!isset($options->data[get_class($this)])) {
        $options->data[get_class($this)] = &$this->data;
      } else {
        $this->data = &$options->data[get_class($this)];
        $this->afterload();
      }
      return  true;
    } else {
      return parent::load();
    }
  }
  
  public function save() {
    global $options;
    if ($this->dbversion) {
      unset($this->data['items']);
      return $options->save();
    } else {
      return parent::save();
    }
  }
  
  public function loaditems(array $items) {
    global  $db;
    if (!dbversion) return;
    //исключить из загрузки загруженные посты
    $items = array_diff($items, array_keys($this->items));
    if (count($items) == 0) return;
    $list = implode(',', $items);
    $res = $db->query("select * from $this->thistable where id in ($list)");
    $res->setFetchMode (PDO::FETCH_ASSOC);
    foreach ($res as $item) {
      $this->items[$item['id']] = $item;
    }
  }
  
  public function getcount() {
    if ($this->dbversion) {
      return $this->db->getcount();
    } else {
      return count($this->items);
    }
  }
  
  public function getitem($id) {
    if ($this->dbversion && !isset($this->items[$id])) $this->items[$id] = $this->db->getitem($id);
    if (isset($this->items[$id])) return $this->items[$id];
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
    if ($this->dbversion) return $this->db->idexists($id);
    return isset($this->items[$id]);
  }
  
  public function IndexOf($name, $value) {
    if ($this->dbversion){
      $id = $this->db->findid("$name = ". dbquote($value));
      return $id ? $id : -1;
    }
    
    foreach ($this->items as $id => $item) {
      if ($item[$name] == $value) {
        return $id;
      }
    }
    return -1;
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

class tsingleitems extends titems {
  public static $instances;
  public $id;
  
  public static function instance($class, $id = 0) {
    global $classes;
    if (!isset(self::$instances)) self::$instances = array();
    if (isset(self::$instances[$class][$id]))     return self::$instances[$class][$id];
    $self = $classes->newinstance($class);
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

//classes.php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function __autoload($class) {
  global $classes;
  $classes->_autoload($class);
}

class tclasses extends titems {
  public $classes;
  public $interfaces;
  public $remap;
  public $instances;
  
  public static function instance() {
    global $classes;
    if (!isset($classes)) {
      $class = __class__;
      $classes = new $class();
      $classes->instances[$class] = $classes;
    }
    return $classes;
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
  
  protected function create() {
    parent::create();
    $this->basename = 'classes';
    $this->dbversion = false;
    $this->addmap('classes', array());
    $this->addmap('interfaces', array());
    $this->addmap('remap', array());
    $this->instances = array();
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
      $this->save();
      $instance = $this->getinstance($class);
      if (method_exists($instance, 'install')) $instance->install();
    }
    $this->added($class);
  }
  
  public function delete($clsss) {
    if (isset($this->items[$class])) {
      if (class_exists($class)) {
        $instance = $this->getinstance($class);
        if (method_exists($instance, 'uninstall')) $instance->uninstall();
      }
      unset($this->items[$class]);
      $this->save();
      $this->deleted($ClassName);
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
    global $paths;
    if ($path =$this->getpath($class)) {
      $filename = $path . $this->items[$class][0];
    } elseif (isset($this->interfaces[$class])) {
      $filename = $paths['lib'] . $this->interfaces[$class];
    } else {
      //$this->error("$class class not found");
      return false;
    }
    if (@file_exists($filename)) require_once($filename);
  }
  
  public function getpath($class) {
    global  $paths;
    if (!isset($this->items[$class])) return false;
    if (empty($this->items[$class][1])) return $paths['lib'];
    
    $result = rtrim($this->items[$class][1], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    if (@is_dir($result))  return $result;
    
    //may be is subdir?
    if (@is_dir($paths['plugins']. $result)) return $paths['plugins']. $result;
    if (@is_dir($paths['themes']. $result)) return $paths['themes']. $result;
    if  (@is_dir($paths['home'] . $result)) return  $paths['home'] . $result;
    
    return false;
  }
  
}//class

function getinstance($class) {
  global $classes;
  return $classes->getinstance($class);
}

function PHPComment($s) {
  $s = str_replace('*/', '**//*/', $s);
  return "<?php /* $s */ ?>";
}

function PHPUncomment($s) {
  $s = substr($s, 9, strlen($s) - 9 - 6);
  return str_replace('**//*/', '*/', $s);
}

function strbegin($s, $begin) {
  return strncmp($s, $begin, strlen($begin)) == 0;
}

function SafeSaveFile($BaseName, $Content) {
  $TmpFileName = $BaseName.'.tmp.php';
  if(!file_put_contents($TmpFileName, $Content))  return false;
  @chmod($TmpFileName , 0666);
  $FileName = $BaseName.'.php';
  if (@file_exists($FileName)) {
    $BakFileName = $BaseName . '.bak.php';
    @unlink($BakFileName);
    rename($FileName, $BakFileName);
  }
  return rename($TmpFileName, $FileName);
}

//options.class.php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class toptions extends tevents {
  public $user;
  public $group;
  public $gmt;
  public $errorlog;
  private $modified;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'options';
    $this->addevents('changed', 'PostsPerPageChanged');
    unset($this->cache);
    $this->gmt = date('Z');
    $this->errorlog = '';
    $this->modified = false;
  }
  
  public function load() {
    parent::load();
    $this->modified = false;
    if($this->propexists('timezone'))  {
      date_default_timezone_set($this->timezone);
      $this->gmt = date('Z');
    }
  }
  
  public function savemodified() {
    if ($this->modified) parent::save();
  }
  
  public function save() {
    $this->modified = true;
  }
  
  public function unlock() {
    $this->modified = true;
    parent::unlock();
  }
  
  public function __set($name, $value) {
    if ($this->setevent($name, $value)) return true;
    
    if (!array_key_exists($name, $this->data)  || ($this->data[$name] != $value)) {
      $this->data[$name] = $value;
      $this->save();
      $this->dochanged($name, $value);
    }
    return true;
  }
  
  private function dochanged($name, $value) {
    if ($name == 'postsperpage') {
      $this->PostsPerPageChanged();
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
  
  public function geturl() {
    if ($this->fixedurl) return $this->data['url'];
    return 'http://'. $GLOBALS['domain'];
  }
  
  public function seturl($url) {
    $url = rtrim($url, '/');
    $this->lock();
    $this->data['url'] = $url;
    $this->files= $url;
    $this->subdir = '';
    if ($i = strpos($url, '/', 10)) {
      $this->subdir = substr($url, $i);
    }
    $this->unlock();
  }
  
  public function auth($login, $password) {
    if ($login == $this->login) {
      $this->user = 1;
    } else {
      $users = tusers::instance();
      if (!($this->user = $users->loginexists($login))) return false;
    }
    
    if ($this->password != md5("$login:$this->realm:$password"))  return false;
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
  
  public function SetPassword($value) {
    $this->password = md5("$this->login:$this->realm:$value");
  }
  
  public function Getinstalled() {
    return isset($this->data['url']);
  }
  
  public function settimezone($value) {
    if(!isset($this->data['timezone']) || ($this->timezone != $value)) {
      $this->data['timezone'] = $value;
      $this->save();
      date_default_timezone_set($this->timezone);
      $this->gmt = date('Z');
    }
  }
  
  public function handexception($e) {
    global $paths;
    $trace =str_replace($paths['home'], '', $e->getTraceAsString());
    $message = "Caught exception:\n" . $e->getMessage();
    $log = $message . "\n" . $trace;
    tfiler::log($log, 'exceptions.log');
    $urlmap = turlmap::instance();
    if (defined('debug') || $this->echoexception || $urlmap->admin) {
      $this->errorlog .= str_replace("\n", "<br />\n", htmlspecialchars($log));
    } else {
      tfiler::log($log, 'exceptionsmail.log');
    }
  }
  
}//class

//urlmap.class.php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class turlmap extends titems {
  public $host;
  public $url;
  public $urlid;
  public $page;
  public $uripath;
  public $itemrequested;
  public $argtree;
  public $is404;
  public $admin;
  public $mobile;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->dbversion = dbversion;
    $this->table = 'urlmap';
    $this->basename = 'urlmap';
    $this->addevents('beforerequest', 'afterrequest', 'CacheExpired');
    $this->is404 = false;
    $this->admin = false;
    $this->mobile= false;
  }
  
  protected function prepareurl($host, $url) {
    global $options;
    $this->host = $host;
    $this->page = 1;
    $this->uripath = array();
    if ($options->q == '?') {
      $this->url = substr($url, strlen($options->subdir));
    } else {
      $this->url = $_GET['url'];
    }
  }
  
  public function request($host, $url) {
    global $options;
    $this->prepareurl($host, $url);
    $this->admin = strbegin($this->url, '/admin/') || ($this->url == '/admin');
    $this->beforerequest();
    try {
      $this->dorequest($this->url);
    } catch (Exception $e) {
      $options->handexception($e);
    }
    $this->afterrequest($this->url);
    $this->CheckSingleCron();
  }
  
  protected function dorequest($url) {
    if ($this->itemrequested = $this->finditem($url)){
      return $this->printcontent($this->itemrequested);
    } else {
      $this->notfound404();
    }
  }
  
  private function query($url) {
    if (dbversion) {
      if ($res = $this->db->select('url = '. $this->db->quote($url). ' limit 1')) {
        $item = $res->fetch(PDO::FETCH_ASSOC);
        $this->items[$item['id']] = $item;
        return $item;
      }
    } elseif (isset($this->items[$url])) return $this->items[$url];
    return false;
  }
  
  public function finditem($url) {
    global $options;
    //redir multi slashed
    if ('//' == substr($url, strlen($url) - 3)) $this->redir301(rtrim($url, '/') . '/');
    
    if ($result = $this->query($url)) return $result;
    
    $slashed = rtrim($url, '/');
    if ($result = $this->query($slashed)) {
      if ($this->page == 1) {
        return $this->redir301($slashed);
      } else {
        return $result;
      }
    }
    
    $slashed  .= '/';
    if ($result = $this->query($slashed)) {
      if ($this->page == 1) {
        return $this->redir301($slashed);
      } else {
        return $result;
      }
    }
    
    if (($options->q == '?') && ($i = strpos($url, '?')) ) {
      $url = substr($url, 0, $i);
      return $this->finditem($url);
    }
    
    //check page number as  /page/page/
    if (count($this->uripath) == 0) {
      $this->uripath = explode('/', trim($url, '/'));
      $c = count($this->uripath);
      if (($c >=2) && ($this->uripath[$c - 2] == 'page') && is_numeric($this->uripath[$c - 1])) {
        $this->page = (int) $this->uripath[$c - 1];
        $url = substr($url, 0, strpos($url, "page/$this->page"));
        array_splice($this->uripath, $c - 2, 2);
        return $this->finditem($url);
      }
    }
    
    //tree обрезаю окончание урла в аргумент
    $url = trim($url, '/');
    $j = -1;
    while($i = strrpos($url, '/', $j)) {
      if ($result = $this->query('/' . substr($url, 0, $i + 1))) {
        $this->argtree = substr($url, $i +1);
        return $result;
      }
      $j = - (strlen($url) - $i + 1);
    }
    
    return false;
  }
  
  private function getcachefile(array $item) {
    global $paths;
    if ($item['type'] == 'normal') {
      return $paths['cache']. sprintf('%s-%d.php', $item['id'], $this->page);
    } else {
      return $paths['cache']. sprintf('%s-%d-%s.php', $item['id'], $this->page, md5($this->url));
    }
  }
  
  protected function  printcontent(array $item) {
    global $options;
    if ($options->cache) {
      $cachefile = $this->getcachefile($item);
      //@file_exists($CacheFileName)
      if (($time = @filemtime ($cachefile)) && (($time  + $options->expiredcache) >= time() )) {
        include($cachefile);
        return;
      }
    }
    
    if (class_exists($item['class']))  {
      return $this->GenerateHTML($item);
    } else {
      $this->deleteclass($item['class']);
      $this->notfound404();
    }
  }
  
  protected function GenerateHTML(array $item) {
    global $options, $template;
    $source = getinstance($item['class']);
    //special handling for rss
    if (method_exists($source, 'request') && ($s = $source->request($item['arg']))) {
      if ($s == 404) return $this->notfound404();
    } else {
      $template = ttemplate::instance();
      $s = $template->request($source);
    }
    eval('?>'. $s);
    if ($options->cache && $source->cache) {
      $cachefile = $this->getcachefile($item);
      file_put_contents($cachefile, $s);
      @chmod($cachefile, 0666);
    }
  }
  
  public function notfound404() {
    $redir = tredirector::instance();
    if (isset($redir->items[$this->url])) {
      return $this->redir301($redir->items[$this->url]);
    }
    
    $this->is404 = true;
    $obj = tnotfound404::instance();
    $Template = ttemplate::instance();
    $s = &$Template->request($obj);
    eval('?>'. $s);
  }
  
  
  public function urlexists($url) {
    if (dbversion) {
      return $this->db->exists('url = '. dbquote($url));
    } else {
      return isset($this->items[$url]);
    }
  }
  public function add($url, $class, $arg, $type = 'normal') {
    if (dbversion) {
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
      $this->db->delete('url = '. $this->db->quote($url));
    } elseif (isset($this->items[$url])) {
      unset($this->items[$url]);
      $this->save();
    }
    $this->clearcache();
  }
  
  public function deleteclass($class) {
    if (dbversion){
      $this->db->delete("class = `$class`");
    } else  {
      foreach ($this->items as $url => $item) {
        if ($item['class'] == $class) unset($this->items[$url]);
      }
      $this->save();
    }
    $this->clearcache();
  }
  
  public function deleteitem($id) {
    if (dbversion){
      $this->db->iddelete($id);
    } else  {
      foreach ($this->items as $url => $item) {
        if ($item['id'] == $id) {
          unset($this->items[$url]);
          $this->save();
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
    global $paths;
    $path = $paths['cache'];
    if ( $h = @opendir($path)) {
      while(FALSE !== ($filename = @readdir($h))) {
        if (($filename == '.') || ($filename == '..') || ($filename == '.svn')) continue;
        $file = $path. $filename;
        if (@is_dir($file)) {
          tfiler::delete($file . DIRECTORY_SEPARATOR, true, true);
        } else {
          unlink($file);
        }
      }
      @closedir($h);
    }
    
    $this->CacheExpired();
  }
  
  public function setexpired($id) {
    global $paths;
    tfiler::deletemask($paths['cache'] . "*.$id-*.php");
  }
  
  public function setexpiredcurrent() {
    @unlink($this->getcachefile($this->itemrequested));
  }
  
  public function getcachename($name, $id) {
    global $paths;
    return $paths['cache']. "$prefix-$id.php";
  }
  
  public function expiredname($name, $id) {
    global $paths;
    tfiler::deletedirmask($paths['cache'], "*$name-$id.php");
  }
  
  public function addredir($from, $to) {
    if ($from == $to) return;
    $Redir = &tredirector::instance();
    $Redir->add($from, $to);
  }
  
  public static function unsub(&$obj) {
    $self = self::instance();
    $self->lock();
    $self->unsubscribeclassname(get_class($obj));
    $self->deleteclass(get_class($obj));
    $self->unlock();
  }
  
  protected function CheckSingleCron() {
    if (defined('cronpinged')) return;
    global $paths;
    $cronfile =$paths['data'] . 'cron' . DIRECTORY_SEPARATOR.  'crontime.txt';
    $time = @filemtime($cronfile);
    if (($time === false) || ($time + 3600 < time())) {
      register_shutdown_function('tcron::selfping');
    }
  }
  
  public function redir301($to) {
    global $options;
    self::redir($options->url . $to);
  }
  
  public static function redir($url) {
    if ( php_sapi_name() != 'cgi-fcgi' ) {
      $protocol = $_SERVER["SERVER_PROTOCOL"];
      if ( ('HTTP/1.1' != $protocol) && ('HTTP/1.0' != $protocol) ) $protocol = 'HTTP/1.0';
      @header( "$protocol 301 Moved Permanently", true, 301);
    }
    
    @header("Location: $url");
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
  
}//class

//interfaces.php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

interface itemplate {
  public function request($arg);
  public function gettitle();
  public function gethead();
  public function getkeywords();
  public function getdescription();
  public function GetTemplateContent();
}

interface itemplate2 {
  public function getsitebar();
  public function afterrequest(&$content);
}

interface imenu {
  public function getparent();
  public function setparent($id);
  public function getorder();
  public function setorder($order);
}

interface imultimedia {
}

?>