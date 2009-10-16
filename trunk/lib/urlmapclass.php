<?php

class turlmap extends TItems {
  public $host;
  public $url;
  public $urlid;
  public $page;
  public $is404;
  public $admin;
  public $mobile;
  private $argfinal;
  
  public static function instance() {
    return getnamedinstance('urlmap', __class__);
  }
  
  protected function create() {
    parent::create();
$this->table = 'urlmap';
    $this->basename = 'urlmap';
    $this->addevents('BeforeRequest', 'AfterRequest', 'CacheExpired');
    $this->is404 = false;
    $this->admin = false;
    $this->mobile= false;
  }
  
  public function request($host, $url) {
    global $options, $paths;
    $this->host = $host;
    $this->page = 1;
    if ($options->q == '?') {
      $this->url = substr($url, strlen($options->subdir));
    } else {
      $this->url = $_GET['url'];
    }
    $this->BeforeRequest();
    if ($this->mobile = (strncmp('/pda/', $this->url, strlen('/pda/')) == 0) || ($this->url == '/pda')) {
      if ($this->url == '/pda') {
        $this->url = '/';
      } else {
        $this->url = substr($this->url, strlen('/pda'));
      }
      $paths['cache'] .= 'mobile' . DIRECTORY_SEPARATOR;
    }
    $this->admin = (strncmp('/admin/', $this->url, strlen('/admin/')) == 0) || ($this->url == '/admin');
    
    try {
      $this->DoRequest($this->url);
    } catch (Exception $e) {
      $options->HandleException($e);
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
    if ($item = $this->finditem($url)) return $this->PrintContent($item);
    $this->NotFound404();
  }
  
  public function finditem($url) {
    global $options;
    //redir multi slashed
    if ('//' == substr($url, strlen($url) - 3)) $this->redir301(rtrim($url, '/') . '/');
    
    if ($this->dbversion) {
      if ($res = $this->db->select('url = '. $this->db->quote($url). ' limit 1')) {
        $item = $res->fetch(PDO::FETCH_ASSOC);
        $this->items[$item['id']] = $item;
        return $item;
      } else {
      return false;
}
    }
    

    if (isset($this->items[$url])) return $this->items[$url];
    $slashed = rtrim($url, '/');
    if (isset($this->items[$slashed])) {
      if ($this->page == 1) {
        return $this->redir301($slashed);
      } else {
        return $this->items[$slashed];
      }
    }
    
    $slashed  .= '/';
    if (isset($this->items[$slashed])) {
      if ($this->page == 1) {
        return $this->redir301($slashed);
      } else {
        return $this->items[$slashed];
      }
    }
    
    if (($options->q == '?') && ($i = strpos($url, '?')) ) {
      $url = substr($url, 0, $i);
    }
    
    if (isset($this->get[$url])) return $this->get[$url];
    
    $slashed = rtrim($url, '/');
    if (isset($this->get[$slashed])) {
      if ($this->page == 1) {
        return $this->redir301($slashed);
      } else {
        return $this->get[$slashed];
      }
    }
    
    $slashed  .= '/';
    if (isset($this->get[$slashed])) {
      if ($this->page == 1) {
        return $this->redir301($slashed);
      } else {
        return $this->get[$slashed];
      }
    }
    
    //check page number as  /page/page/
    $this->uripath = $this->ParseUriPath($url);
    $c = count($this->uripath);
    if (($c >=2) && ($this->uripath[$c - 2] == 'page') && is_numeric($this->uripath[$c - 1])) {
      $this->page = (int) $this->uripath[$c - 1];
      $url = substr($url, 0, strpos($url, "page/$this->page"));
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
    global $options, $paths;
    $this->urlid = $item['id'];
    if ($options->CacheEnabled) {
  $CacheFileName = "{$paths['cache']}{$item['id']}-$this->page.php";
      //@file_exists($CacheFileName)
      if (($time = @filemtime ($CacheFileName)) && (($time  + $options->CacheExpired) >= time() )) {
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
    global $options, $paths, $Template;
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
    if ($options->CacheEnabled && $obj->CacheEnabled) {
  $CacheFileName = "{$paths['cache']}{$item['id']}-$this->page.php";
      file_put_contents($CacheFileName, $s);
      @chmod($CacheFileName, 0666);
    }
  }
  
  public function NotFound404() {
    $redir = &TRedirector ::Instance();
    if (isset($redir->items[$this->url])) {
      return $this->redir301($redir->items[$this->url]);
    }
    
    $this->is404 = true;
    $obj = &TNotFound404::Instance();
    $Template = &TTemplate::Instance();
    $s = &$Template->Request($obj);
    eval('?>'. $s);
  }
  
  public function add($url, $class, $arg, $type = 'nornal') {
if (dbversion) {
$item array(
    'class' => $class,
    'arg' => $arg,
'type' => $type
    );
$item['id'] = $this->db->InsertAssoc($item);
$this->items[$item['id']] = $item;
return $item['id'];
}
    $this->items[$url] = array(
    'id' => ++$this->lastid,
    'class' => $class,
    'arg' => $arg,
'type' => $type
    );
    $this->save();
    return $this->lastid;
  }
 
  public function delete($url) {
if (dbversion) {
$this->db->delete('url = '. $this->db->quote($url));
} else {
if (isset($this->items[$url)) {
unset($this->items[$url]);
$this->save();
}
}
  }
  
  public function DeleteClassArg($class, $arg) {
if (dbversion) return $this->db->delete("class = '$class' and arg = ". $this->db->quote($arg));

    foreach ($this->items as  $url => $item) {
      if (($item['class'] == $class) && ($item['arg'] == $arg)) {
        unset($items[$url]);
        return true;
      }
    }
    return false;
  }
  
//uses TArchives
  public function GetClassUrls($class) {
if (dbversion) {
$res = $this->db->query("select url from $this->thistable where class = '$class'");
return $this->db->res2array($res);
}

    $result = array();
    foreach ($this->items as $url => $item) {
      if ($item['class'] == $class) $result[] = $url;
    }
    return $result;
  }
  
  public function DeleteClass($class) {
if (dbversion)  return $this->db->delete("class = `$class`");

    foreach ($this->items as $url => $item) {
      if ($item['class'] == $class) {
        unset($items[$url]);
        $this->unlink($item['id']. '-1.php');
      }
    }
  }
  
  public function clearcache() {
    global $paths;
    if ($this->mobile) {
      TFiler::DeleteFiles(dirname(dirname($paths['cache'])) . DIRECTORY_SEPARATOR, true, false);
    } else {
      TFiler::DeleteFiles($paths['cache'], true, false);
    }
    $this->CacheExpired();
  }
  
  private function unlink($filename) {
    global $paths;
    @unlink($paths['cache'] . $filename);
    if ($this->mobile) {
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
  
  public function addredir($from, $to) {
    if ($from == $to) return;
    $this->lock();
    $Redir = &TRedirector::Instance();
    $Redir->add($from, $to);
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
  
  public function redir301($to) {
    global $options;
    if ( php_sapi_name() != 'cgi-fcgi' ) {
      $protocol = $_SERVER["SERVER_PROTOCOL"];
      if ( ('HTTP/1.1' != $protocol) && ('HTTP/1.0' != $protocol) )
      $protocol = 'HTTP/1.0';
      @header( "$protocol 301 Moved Permanently", true, 301);
    }
    @header("Location: $options->url$to");
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
  
public function setidulr($id, $url) {
if (dbversion) {
$this->db->setvalue($id, 'url', $url);
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

?>