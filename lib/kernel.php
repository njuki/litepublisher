<?php

//dataclass.php
class TDataClass {
  private $LockCount;
  public static $GlobalLock;
  public $Data;
  public $basename;
  public $CacheEnabled;
  //database
  public $table;
  
  public function __construct() {
    $this->LockCount = 0;
    $this->CacheEnabled = true;
    $this->Data= array();
    $this->basename = 'data';
    $this->CreateData();
  }
  
  protected function CreateData() {
  }
  
  public function __get($name) {
    if (method_exists($this, $get = "Get$name") ||
    method_exists($this, $get = "get$name")) {
      return $this->$get();
    } elseif (array_key_exists($name, $this->Data)) {
      return $this->Data[$name];
    } else {
      return    $this->Error("The requested property $name not found in class ". get_class($this));
    }
  }
  
  public function __set($name, $value) {
    if (method_exists($this, $set = "Set$name")) {
      $this->$set($value);
      return true;
    }
    
    if (key_exists($name, $this->Data)) {
      $this->Data[$name] = $value;
      return true;
    }
    
    return false;
  }
  
  public  function __call($name, $params) {
    if (method_exists($this, strtolower($name))) {
      return call_user_func_array(array(&$this, strtolower($name)), $params);
    }
    $this->Error("The requested method $name not found in class " . get_class($this));
  }
  
  public function PropExists($name) {
    return array_key_exists($name, $this->Data) || method_exists($this, "Get$name") | method_exists($this, "get$name") || isset($this->$name);
  }
  
  public function supported($interface) {
    return is_a($this, $interface);
  }
  
  public function Error($Msg) {
    throw new Exception($Msg);
  }
  
  public function GetBaseName() {
    return $this->basename;
  }
  
  public function Install() {
    $this->CallSatellite('Install');
  }
  
  public function Uninstall() {
    $this->CallSatellite('Uninstall');
  }
  
  public function Validate($repair = false) {
    $this->CallSatellite('Validate', $repair);
  }
  
  protected function CallSatellite($func, $arg = null) {
    global $classes, $paths;
    $parents = class_parents($this);
    array_splice($parents, 0, 0, get_class($this));
    foreach ($parents as $key => $class) {
      if ($path = $classes->GetPath($class)) {
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
    if ($this->dbversion == 'full') return $this->LoadFromDB();
    $FileName = $paths['data'] . $this->GetBaseName() .'.php';
    if (@file_exists($FileName)) {
      return $this->LoadFromString(PHPUncomment(file_get_contents($FileName)));
    }
  }
  
  public function save() {
    global $paths;
    if (self::$GlobalLock || ($this->LockCount > 0)) return;
    if ($this->dbversion == 'full') {
      $this->SaveToDB();
    } else {
      SafeSaveFile($paths['data'].$this->GetBaseName(), $this->SaveToString());
    }
  }
  
  public function SaveToFile($FileName) {
    if ($fh = fopen($FileName, 'w+')) {
      $this->SaveToStream($fh);
      fclose($fh);
    } else {
      $this->Error("Cannt open $FileName to write");
    }
  }
  
  public function SaveToStream($handle) {
    $s = $this->SaveToString();
    fwrite($handle, $s);
  }
  
  public function LoadFromFile($FileName) {
    if ($fh = fopen($FileName, 'r')) {
      $this->LoadFromStream($fh, filesize($FileName));
      fclose($fh);
    } else {
      $this->Error("Cant open $FileName to read");
    }
  }
  
  public function  LoadFromStream($handle, $length) {
    $s = fread($handle,  $length);
    $this->LoadFromString($s);
  }
  
  public function SaveToString() {
    return PHPComment(serialize($this->Data));
  }
  
  public function LoadFromString($s) {
    try {
      if (!empty($s)) $this->Data = unserialize($s) + $this->Data;
      $this->AfterLoad();
    } catch (Exception $e) {
      echo 'Caught exception: '.  $e->getMessage() ;
    }
  }
  
  public function AfterLoad() {
  }
  
  public function lock() {
    $this->LockCount++;
  }
  
  public function Unlock() {
    if (--$this->LockCount <= 0) $this->Save();
  }
  
  public function Getlocked() {
    return $this->LockCount  > 0;
  }
  
  public function Getdbversion() {
    return false;
  }
  
  public function Getclass() {
    return get_class($this);
  }
  
  public function Getdb($table = 'data') {
    global $db;
    $table =$table != '' ? $table : $this->table;
    if ($table != '') $db->table = $table;
    return $db;
  }
  
  protected function SaveToDB() {
    $db->add($this->GetBaseName(), $this->SaveToString());
  }
  
  protected function LoadFromDB() {
    if ($r = $this->db->select('basename = '. $this->GetBaseName() . "'")) {
      return $this->LoadFromString($r['data']);
    }
  }
  
}//class

//eventclass.php
class TEventClass extends TDataClass {
  protected $events;
  protected $EventNames;
  protected $DataMap;
  
  public function __construct() {
    $this->EventNames = array();
    $this->DataMap = array();
    parent::__construct();
    $this->AssignDataMap();
    $this->load();
  }
  
  public function free() {
    global $classes;
    unset($classes->instances[get_class($this)]);
  }
  
  protected function CreateData() {
    if (!$this->dbversion) $this->AddDataMap('events', array());
  }
  
  public function AssignDataMap() {
    foreach ($this->DataMap as $propname => $key) {
      $this->$propname = &$this->Data[$key];
    }
  }
  
  public function AfterLoad() {
    $this->AssignDataMap();
  }
  
  protected function AddDataMap($name, $value) {
    $this->DataMap[$name] = $name;
    $this->Data[$name] = $value;
    $this->$name = &$this->Data[$name];
  }
  
  public function __get($name) {
    if (method_exists($this, $name)) {
      return array(
      'class' =>get_class($this),
      'func' => $name
      );
    }
    
    return parent::__get($name);
  }
  
  public function __set($name, $value) {
    if (parent::__set($name, $value)) return true;
    if ($this->SetEvent($name, $value)) return true;
    $this->Error("Unknown property $name in class ". get_class($this));
  }
  
  protected function SetEvent($name, $value) {
    if (in_array($name, $this->EventNames)) {
      $this->SubscribeEvent($name, $value);
      return true;
    }
    return false;
  }
  
  public  function __call($name, $params) {
    if (in_array($name, $this->EventNames)) {
      return $this->CallEvent($name, $params);
    }
    
    parent::__call($name, $params);
  }
  
  protected function AddEvents() {
    $a = func_get_args();
    array_splice($this->EventNames, count($this->EventNames), 0, $a);
  }
  
  private function GetEvents($name) {
    if (isset($this->events[$name])) return $this->events[$name];
    if ($this->dbversion) {
      $r = $this->db->SelectTableWhere('events', "owner = '$this->class' and name = '$name'");
      $this->events[$name] = $r->fetchAll (PDO::FETCH_ASSOC);
      return $this->events[$name];
    }
    return false;
  }
  
  private function CallEvent($name, &$params) {
    $Result = '';
    if (    $list = $this->GetEvents($name)) {
      foreach ($list as $i => $item) {
        if (empty($item['class'])) {
          if (function_exists($item['func'])) {
            $call = $item['func'];
          } else {
            $this->DeleteEvent($name, $i);
            continue;
          }
        } elseif (!class_exists($item['class'])) {
          $this->DeleteEvent();
          continue;
        } else {
          $obj = &GetInstance($item['class']);
          $call = array(&$obj, $item['func']);
        }
        $lResult = call_user_func_array($call, $params);
        if (is_string($lResult)) $Result .= $lResult;
      }
    }
    
    return $Result;
  }
  
  private function DeleteEvent($name, $i) {
    if ($this->dbversion) {
      $id =           $this->events[$name][$i]['id'];
      $db = $this->Getdb('events');
      $db->deleteid($id);
      array_splice($this->events[$name], $i, 1);
    } else {
      array_splice($this->events[$name], $i, 1);
      $this->save();
    }
  }
  
  public function SubscribeEvent($name, $params) {
    if (!isset($this->events[$name])) $this->events[$name] =array();
    foreach ($this->events[$name] as $event) {
      if (($event['class'] == $params['class']) && ($event['func'] == $params['func'])) return;
    }
    
    $this->events[$name][] = array(
    'class' => $params['class'],
    'func' => $params['func']
    );
    if ($this->dbversion) {
      $event = &$this->events[$name][count($this->events[$name]) - 1];
      $event['name'] = $name;
      $event['owner'] = get_class($this);
      $db = $this->Getdb('events');
      $event['id'] = $db->InsertAssoc($event);
    } else {
      $this->save();
    }
  }
  
  public function UnsubscribeEvent($name, $class) {
    if (isset($this->events[$name])) {
      foreach ($this->events[$name] as $i => $item) {
        if ($item['class'] == $class) {
          $this->DeleteEvent($name, $i);
          return true;
        }
      }
    }
    return false;
  }
  
  public static function unsub(&$obj) {
    $self = self::Instance();
    $self->UnsubscribeClassName(get_class($obj));
  }
  
  public function UnsubscribeClass(&$obj) {
    $this->UnsubscribeClassName(get_class($obj));
  }
  
  public function UnsubscribeClassName($class) {
    $this->lock();
    foreach ($this->events as $name => $events) {
      foreach ($events as $i => $item) {
        if ($item['class'] == $class)  $this->DeleteEvent($name, $i);
      }
    }
    $this->unlock();
  }
  
  public function Validate() {
    foreach ($this->EventNames as $name) {
      if (Method_exists($this, $name)) $this->Error("the virtual method $name cannt be exist in class". get_class($this));
    }
  }
  
}

//itemsclass.php
class TItems extends TEventClass {
  public $items;
  protected $lastid;
  
  protected function CreateData() {
    parent::CreateData();
    $this->AddEvents('Added', 'Deleted');
    $this->AddDataMap('items', array());
    $this->AddDataMap('lastid', 0);
  }
  
  public function Getcount() {
    if ($this->dbversion) {
      return $this->db->getcount();
    } else {
      return count($this->items);
    }
  }
  
  public function GetItem($id) {
    if ($this->dbversion && !isset($this->items[$id])) {
      if ($res = $this->db->select("id = $id")) {
        $this->items[$id] = $res->fetch(PDO::FETCH_ASSOC);
      }
    }
    
    if (isset($this->items[$id])) {
      return $this->items[$id];
    }
    return $this->Error("Item $id not found in class ". get_class($this));
  }
  
  public function GetValue($id, $name) {
    return $this->items[$id][$name];
  }
  
  public function SetValue($id, $name, $value) {
    $this->items[$id][$name] = $value;
  }
  
  public function ItemExists($id) {
    return isset($this->items[$id]);
  }
  
  public function IndexOf($name, $value) {
    foreach ($this->items as $id => $item) {
      if ($item[$name] == $value) {
        return $id;
      }
    }
    return -1;
  }
  
  public function delete($id) {
    if ($this->dbversion) {
      $this->db->delete("id = $id");
      if (isset($this->items[$id])) unset($this->items[$id]);
    } else {
      if (isset($this->items[$id])) {
        unset($this->items[$id]);
        $this->save();
        $this->Deleted($id);
        return true;
      }
      return false;
    }
  }
  
}

//classes.php
function __autoload($ClassName) {
  global $classes;
  if ($path =$classes->GetPath($ClassName)) {
    $filename = $path . $classes->items[$ClassName][0];
    if (@file_exists($filename)) {
      require_once($filename);
    }
  }
}

class TClasses extends TItems {
  public $classes;
  public $instances;
  
  public static function &Instance() {
    return GetInstance(__class__);
  }
  
  protected function CreateData() {
    parent::CreateData();
    $this->basename = 'classes';
    $this->AddDataMap('classes', array());
    $this->instances = array();
  }
  
  public function Add($ClassName, $FileName, $Path = '') {
    if (!isset($this->items[$ClassName]) ||
    ($this->items[$ClassName][0] != $FileName) || ($this->items[$ClassName][1] != $Path)) {
      $this->items[$ClassName] = array($FileName, $Path);
      $this->Save();
      $instance = &GetInstance($ClassName);
      if (method_exists($instance, 'Install')) $instance->Install();
    }
    $this->Added($ClassName);
  }
  
  public function Delete($ClassName) {
    if (isset($this->items[$ClassName])) {
      if (class_exists($ClassName)) {
        $instance = &GetInstance($ClassName);
        if (method_exists($instance, 'Uninstall')) $instance->Uninstall();
      }
      unset($this->items[$ClassName]);
      $this->Save();
      $this->Deleted($ClassName);
    }
  }
  
  public function Reinstall($class) {
    if (isset($this->items[$class])) {
      $this->Lock();
      $item = $this->items[$class];
      $this->Delete($class);
      $this->Add($class, $item[0], $item[1]);
      $this->Unlock();
    }
  }
  
  public function GetPath($class) {
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

function &GetInstance($ClassName) {
  global $classes;
  if (!class_exists($ClassName)) {
    $classes->Error("Class $ClassName not found");
  }
  if (!isset($classes->instances[$ClassName])) {
    $classes->instances[$ClassName] = &new $ClassName ();
  }
  return $classes->instances[$ClassName];
}

function &GetNamedInstance($name, $defclass) {
  global $classes;
  $class = !empty($classes->classes[$name]) ? $classes->classes[$name] : $defclass;
  return GetInstance($class);
}

function PHPComment(&$s) {
  $s = str_replace('*/', '**//*/', $s);
  return "<?php /* $s */ ?>";
}

function PHPUncomment(&$s) {
  $s = substr($s, 9, strlen($s) - 9 - 6);
  return str_replace('**//*/', '*/', $s);
}

function SafeSaveFile($BaseName, &$Content) {
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

//optionsclass.php
class TOptions extends TEventClass {
  
  public static function &Instance() {
    return GetInstance(__class__);
  }
  
  protected function CreateData() {
    parent::CreateData();
    $this->basename = 'options';
    $this->AddEvents('Changed', 'PostsPerPageChanged', 'OnGeturl');
    unset($this->CacheEnabled);
  }
  
  public function Load() {
    parent::Load();
    if($this->PropExists('timezone'))  {
      date_default_timezone_set($this->timezone);
      if ($this->dbversion) $this->db->exec("SET time_zone = '$this->timezone'");
    }
    if (!defined('gmt_offset')) define('gmt_offset', date('Z'));
  }
  
  public function __set($name, $value) {
    if ($this->SetEvent($name, $value)) return true;
    
    if (!isset($this->Data[$name]) || ($this->Data[$name] != $value)) {
      $this->Data[$name] = $value;
      $this->Save();
      $this->FieldChanged($name, $value);
    }
    return true;
  }
  
  private function FieldChanged($name, $value) {
    if ($name == 'postsperpage') {
      $this->PostsPerPageChanged();
      $urlmap = &TUrlmap::Instance();
      $urlmap->ClearCache();
    } elseif ($name == 'CacheEnabled') {
      $urlmap = &TUrlmap::Instance();
      $urlmap->ClearCache();
    } else {
      $this->Changed($name, $value);
    }
  }
  
  public function Geturl() {
    $result = $this->OnGeturl();
    if (!empty($result)) return $result;
    $result = $this->Data['url'];
    if ($this->q == '&') $result .= '/index.php?url=';
    $urlmap = TUrlmap::Instance();
    if ($urlmap->Ispda) $result .= '/pda';
    return $result;
  }
  
  public function Seturl($url) {
    $url = rtrim($url, '/');
    $this->Lock();
    $this->Data['url'] = $url;
    $this->files= $url;
    $this->subdir = '';
    if ($i = strpos($url, '/', 10)) {
      $this->subdir = substr($url, $i);
    }
    $this->Unlock();
  }
  
  public function CheckLogin($login, $password) {
    return $this->password == md5("$login:$this->realm:$password");
  }
  
  public function Auth(){
    if (isset($_SERVER['PHP_AUTH_USER'])) {
      return $this->CheckLogin($_SERVER['PHP_AUTH_USER'] , $_SERVER['PHP_AUTH_PW']);
    }
    return false;
  }
  
  public function SetPassword($value) {
    $this->password = md5("$this->login:$this->realm:$value");
  }
  
  public function Getinstalled() {
    return isset($this->Data['url']);
  }
  
  public function HandleException(&$e) {
    global $paths;
    $trace =str_replace($paths['home'], '', $e->getTraceAsString());
    $message = 'Caught exception: ' . $e->getMessage();
    $log = $message . "\n" . $trace;
    TFiler::log($log, 'exceptions.log');
    $urlmap = TUrlmap::Instance();
    if (defined('debug') || $this->echoexception || $urlmap->IsAdminPanel) {
      echo str_replace("\n", "<br />\n", htmlspecialchars($log));
    } else {
      TFiler::log($log, 'exceptionsmail.log');
    }
  }
  
}//class

//urlmapclass.php
class TUrlmap extends TItems {
  public $host;
  public $url;
  public $urlid;
  public $uripath;
  public $pagenumber;
  public $get;
  public $tree;
  public $is404;
  public $IsAdminPanel;
  public $Ispda;
  private $argfinal;
  
  public static function &Instance() {
    return GetNamedInstance('urlmap', __class__);
  }
  
  protected function CreateData() {
    parent::CreateData();
    $this->basename = 'urlmap';
    $this->AddEvents('BeforeRequest', 'AfterRequest', 'CacheExpired');
    $this->AddDataMap('get', array());
    $this->AddDataMap('tree', array());
    $this->is404 = false;
    $this->IsAdminPanel = false;
    $this->Ispda= false;
  }
  
  public function Request($host, $url) {
    global $Options, $paths;
    $this->host = $host;
    $this->pagenumber = 1;
    if ($Options->q == '?') {
      $this->url = substr($url, strlen($Options->subdir));
    } else {
      $this->url = $_GET['url'];
    }
    $this->BeforeRequest();
    if ($this->Ispda = (strncmp('/pda/', $this->url, strlen('/pda/')) == 0) || ($this->url == '/pda')) {
      if ($this->url == '/pda') {
        $this->url = '/';
      } else {
        $this->url = substr($this->url, strlen('/pda'));
      }
      $paths['cache'] .= 'pda' . DIRECTORY_SEPARATOR;
    }
    $this->IsAdminPanel = (strncmp('/admin/', $this->url, strlen('/admin/')) == 0) || ($this->url == '/admin');
    
    try {
      $this->DoRequest($this->url);
    } catch (Exception $e) {
      $Options->HandleException($e);
    }
    $this->AfterRequest($this->url);
    $this->CheckSingleCron();
  }
  
  protected function ParseUriPath($url) {
    $url = trim($url, '/');
    $result = array();
    while ($i = strpos($url, '/')) {
      $result[] = substr($url, 0, $i);
      $url = substr($url, $i + 1);
    }
    $result[] = $url;
    return $result;
  }
  
  protected function DoRequest($url) {
    if ($item = &$this->FindItem($url)) {
      return $this->PrintContent($item);
    }
    $this->NotFound404();
  }
  
  public function &FindItem($url) {
    global $Options;
    //redir multi slashed
    if ('//' == substr($url, strlen($url) - 3)) $this->Redir301(rtrim($url, '/') . '/');
    
    //4 steps: items, get, pagenumber, tree
    if (isset($this->items[$url])) return $this->items[$url];
    $slashed = rtrim($url, '/');
    if (isset($this->items[$slashed])) {
      if ($this->pagenumber == 1) {
        return $this->Redir301($slashed);
      } else {
        return $this->items[$slashed];
      }
    }
    
    $slashed  .= '/';
    if (isset($this->items[$slashed])) {
      if ($this->pagenumber == 1) {
        return $this->Redir301($slashed);
      } else {
        return $this->items[$slashed];
      }
    }
    
    if (($Options->q == '?') && ($i = strpos($url, '?')) ) {
      $url = substr($url, 0, $i);
    }
    
    if (isset($this->get[$url])) return $this->get[$url];
    
    $slashed = rtrim($url, '/');
    if (isset($this->get[$slashed])) {
      if ($this->pagenumber == 1) {
        return $this->Redir301($slashed);
      } else {
        return $this->get[$slashed];
      }
    }
    
    $slashed  .= '/';
    if (isset($this->get[$slashed])) {
      if ($this->pagenumber == 1) {
        return $this->Redir301($slashed);
      } else {
        return $this->get[$slashed];
      }
    }
    
    //check page number as  /page/pagenumber/
    $this->uripath = $this->ParseUriPath($url);
    $c = count($this->uripath);
    if (($c >=2) && ($this->uripath[$c - 2] == 'page') && is_numeric($this->uripath[$c - 1])) {
      $this->pagenumber = (int) $this->uripath[$c - 1];
      $url = substr($url, 0, strpos($url, "page/$this->pagenumber"));
      array_splice($this->uripath, $c - 2, 2);
      return $this->FindItem($url);
    }
    
    $null = null;
    
    if (isset($this->tree[$this->uripath[0]])) {
      //walk on tree
      $item = &$this->tree[$this->uripath[0]];
      for ($i = 1; $i <  count($this->uripath); $i++ ) {
        if (isset($item['items'][$this->uripath[$i]])) {
          $item = &$item['items'][$this->uripath[$i]];
        } elseif (isset($item['final'])) {
          $this->argfinal = implode('/', array_slice($this->uripath, $i));
          return $item;
        } else {
          return $null;
        }
      }
      return $item;
    }
    
    return $null;
  }
  
  protected function  PrintContent(&$item) {
    global $Options, $paths;
    $this->urlid = $item['id'];
    if ($Options->CacheEnabled) {
  $CacheFileName = "{$paths['cache']}{$item['id']}-$this->pagenumber.php";
      //@file_exists($CacheFileName)
      if (($time = @filemtime ($CacheFileName)) && (($time  + $Options->CacheExpired) >= time() )) {
        include($CacheFileName);
        return;
      }
    }
    
    $ClassName = $item['class'];
    if (!class_exists($ClassName)) {
      __autoload($ClassName);
      if (!@class_exists($ClassName)) {
        $this->DeleteClass($ClassName);
        return $this->NotFound404();
      }
    }
    $this->PrintClassContent($ClassName, $item);
  }
  
  protected function PrintClassContent($ClassName, &$item) {
    global $Options, $paths, $Template;
    $obj = &GetInstance($ClassName);
    $arg = isset($this->argfinal)  ? $this->argfinal : $item['arg'];
    //special handling for rss
    if (method_exists($obj, 'Request') && ($s = $obj->Request($arg))) {
      if ($s == 404) return $this->NotFound404();
    } else {
      $Template = TTemplate::Instance();
      $s = $Template->request($obj);
    }
    eval('?>'. $s);
    if ($Options->CacheEnabled && $obj->CacheEnabled) {
  $CacheFileName = "{$paths['cache']}{$item['id']}-$this->pagenumber.php";
      file_put_contents($CacheFileName, $s);
      @chmod($CacheFileName, 0666);
    }
  }
  
  public function NotFound404() {
    $redir = &TRedirector ::Instance();
    if (isset($redir->items[$this->url])) {
      return $this->Redir301($redir->items[$this->url]);
    }
    
    $this->is404 = true;
    $obj = &TNotFound404::Instance();
    $Template = &TTemplate::Instance();
    $s = &$Template->Request($obj);
    eval('?>'. $s);
  }
  
  protected function AddItem(&$items, $url, $class, $arg) {
    $items[$url] = array(
    'id' => ++$this->lastid,
    'class' => $class,
    'arg' => $arg
    );
    $this->save();
    return $this->lastid;
  }
  
  public function Add($url, $class, $arg) {
    return $this->AddItem($this->items, $url, $class, $arg);
  }
  
  public function AddGet($url, $class, $arg) {
    return $this->AddItem($this->get, $url, $class, $arg);
  }
  
  public function AddNode($url, $class, $arg) {
    return $this->AddItem($this->tree, $url, $class, $arg);
  }
  
  public function AddSubNode($nodeurl, $url, $class, $arg) {
    if (!isset($this->tree[$nodeurl])) $this->AddNode($nodeurl, $class, null);
    if (!isset($this->tree[$nodeurl]['items'])) $this->tree[$nodeurl]['items'] = array();
    return $this->AddItem($this->tree[$nodeurl]['items'], $url, $class, $arg);
  }
  
  public function AddFinalNode($nodeurl, $url, $class) {
    if (!isset($this->tree[$nodeurl])) $this->Error("node $nodeurl is not exists!");
    if (!isset($this->tree[$nodeurl]['items'])) $this->tree[$nodeurl]['items'] = array();
    $this->tree[$nodeurl]['items'][$url] = array(
    'id' => ++$this->lastid,
    'class' => $class,
    'arg' => null,
    'final' => true
    );
    $this->Save();
    return $this->lastid;
  }
  
  public function AddFinal($url, $class) {
    $this->tree[$url] = array(
    'id' => ++$this->lastid,
    'class' => $class,
    'arg' => null,
    'final' => true
    );
    $this->Save();
    return $this->lastid;
  }
  
  private function DeleteItem(&$items, $url) {
    if (isset($items[$url])) {
      $this->unlink($items[$url]['id'] . '-1.php');
      unset($items[$url]);
      return true;
    }
    return false;
  }
  
  public function Delete($url) {
    if ($this->DeleteItem($this->items, $url) || $this->DeleteItem($this->get, $url) || $this->DeleteItem($this->tree, $url)) {
      $this->Save();
    }
  }
  
  private function DeleteClassArgItem(&$items, $class, $arg) {
    foreach ($items as  $url => $item) {
      if (($item['class'] == $class) && ($item['arg'] == $arg)) {
        unset($items[$url]);
        return true;
      }
    }
    return false;
  }
  
  public function DeleteClassArg($class, $arg) {
    if (!($this->DeleteClassArgItem($this->items, $class, $arg) || $this->DeleteClassArgItem($this->get, $class, $arg))) {
      foreach ($this->tree as $url => $item) {
        if (!isset($this->tree[$url]['items'])) continue;
        if ($this->DeleteClassArgItem($this->tree[$url]['items'], $class, $arg)) break;
      }
    }
    $this->Save();
  }
  
  public function DeleteSubNode($node, $subnode) {
    if ($this->DeleteItem($this->tree[$node]['items'], $subnode)) {
      $this->Save();
    }
  }
  
  public function &GetClassItems($class) {
    $result = array();
    foreach ($this->items as $url => $item) {
      if ($item['class'] == $class) $result[] = $url;
    }
    return $result;
  }
  
  private function RemoveItems(&$items, $class) {
    foreach ($items as $url => $item) {
      if ($item['class'] == $class) {
        $this->unlink($item['id']. '-1.php');
        unset($items[$url]);
      }
    }
  }
  
  public function DeleteClass($class) {
    $this->lock();
    
    $this->RemoveItems($this->items, $class);
    $this->RemoveItems($this->get, $class);
    $this->RemoveItems($this->tree, $class);
    foreach ($this->tree as $url => $item) {
      if (isset($item['items'])) {
        $this->RemoveItems($this->tree[$url]['items'], $class);
      }
    }
    
    $this->unlock();
  }
  
  public function Find($class, $params) {
    foreach ($this->items as $url => $item) {
      if (($item['class']== $class) && ($item['arg'] == $params)) {
        return $url;
      }
    }
    return false;
  }
  
  public function Edit($class, $params, $newurl) {
    if ($url = $this->Find($class, $params)) {
      if ($url == $url) return true;
      if (isset($this->items[$newurl]))  {
        $newurl = TLinkGenerator ::MakeUnique($newurl);
      }
      $this->Replace($url, $newurl);
      return true;
    }
    return false;
  }
  
  public function ClearCache() {
    global $paths;
    if ($this->Ispda) {
      TFiler::DeleteFiles(dirname(dirname($paths['cache'])) . DIRECTORY_SEPARATOR, true, false);
    } else {
      TFiler::DeleteFiles($paths['cache'], true, false);
    }
    $this->CacheExpired();
  }
  
  private function unlink($filename) {
    global $paths;
    @unlink($paths['cache'] . $filename);
    if ($this->Ispda) {
      @unlink(dirname(dirname($paths['cache'])) . DIRECTORY_SEPARATOR . $filename);
    } else {
      @unlink($paths['cache'] . 'pda'. DIRECTORY_SEPARATOR . $filename);
    }
  }
  
  public function SetExpired($url) {
    if (isset($this->items[$url])) {
      $id = $this->items[$url]['id'];
      for ($i = 1; $i <=10; $i++) {
        $this->unlink("$id-$i.php");
      }
    }
  }
  
  public function SubNodeExpired($node, $subnode) {
    if (isset($this->tree[$node]['items'][$subnode])) {
      $this->unlink($this->tree[$node]['items'][$subnode]['id'] . "-$this->pagenumber.php");
    } elseif (isset($this->tree[$node]['final'])) {
      $this->unlink($this->tree[$node]['id']. "-$subnode.php");
    }
  }
  
  public function Replace($old, $new) {
    if ($old == $new) return;
    $this->lock();
    $Redir = &TRedirector::Instance();
    $Redir->Add($old, $new);
    $this->items[$new] = $this->items[$old];
    $this->unlink($this->items[$old]['id'] . '.php');
    unset($this->items[$old]);
    $this->Add($old, get_class($Redir), null);
    $this->unlock();
  }
  
  public function AddRedir($from, $to) {
    if ($from == $to) return;
    $this->lock();
    $Redir = &TRedirector::Instance();
    $Redir->Add($from, $to);
    $this->Add($from, get_class($Redir), null);
    $this->unlock();
  }
  
  public static function unsub(&$obj) {
    $self = self::Instance();
    $self->lock();
    $self->UnsubscribeClassName(get_class($obj));
    $self->DeleteClass(get_class($obj));
    $self->unlock();
  }
  
  protected function CheckSingleCron() {
    if (defined('cronpinged')) return;
    global $paths;
    $cronfile =$paths['data'] . 'cron' . DIRECTORY_SEPARATOR.  'crontime.txt';
    $time = @filemtime($cronfile);
    if (($time === false) || ($time + 3600 < time())) {
      register_shutdown_function('TCron::SelfPing');
    }
  }
  
  public function Redir301($to) {
    global $Options;
    if ( php_sapi_name() != 'cgi-fcgi' ) {
      $protocol = $_SERVER["SERVER_PROTOCOL"];
      if ( ('HTTP/1.1' != $protocol) && ('HTTP/1.0' != $protocol) )
      $protocol = 'HTTP/1.0';
      @header( "$protocol 301 Moved Permanently", true, 301);
    }
    @header("Location: $Options->url$to");
    exit();
  }
  
  public static function redir($url) {
    if ( php_sapi_name() != 'cgi-fcgi' ) {
      $protocol = $_SERVER["SERVER_PROTOCOL"];
      if ( ('HTTP/1.1' != $protocol) && ('HTTP/1.0' != $protocol) )
      $protocol = 'HTTP/1.0';
      @header( "$protocol 301 Moved Permanently", true, 301);
    }
    
    @header("Location: $url");
    exit();
  }
  
}

//interfaces.php
interface ITemplate {
  public function request($arg);
  public function gettitle();
  public function gethead();
  public function getkeywords();
  public function getdescription();
  public function GetTemplateContent();
}

?>