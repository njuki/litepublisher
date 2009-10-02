<?php

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
      $s = &$Template->Request($obj);
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

?>