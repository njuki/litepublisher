<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
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
  public $cache_enabled;
  public $argtree;
  public $is404;
  public $isredir;
  public $adminpanel;
  public $mobile;
  protected $close_events;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = true;
    parent::create();
    $this->table = 'urlmap';
    $this->basename = 'urlmap';
    $this->addevents('beforerequest', 'afterrequest', 'onclearcache');
$this->data['revision'] = 0;
    $this->is404 = false;
    $this->isredir = false;
    $this->adminpanel = false;
    $this->mobile= false;
    $this->cachefilename = false;
    $this->cache_enabled =     litepublisher::$options->cache && !litepublisher::$options->admincookie;
    $this->page = 1;
    $this->close_events = array();
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
    
    if (!litepublisher::$debug && litepublisher::$options->ob_cache) {
      if ($this->isredir || count($this->close_events)) $this->close_connection();
      while (@ob_end_flush ());
      flush();
      //prevent output while client connected
      if ($this->isredir || count($this->close_events)) ob_start();
    }
    $this->afterrequest($this->url);
    $this->close();
  }
  
  public function close_connection() {
    ignore_user_abort(true);
    //$len = $this->isredir ? 0 : ob_get_length();
    $len = ob_get_length();
    header('Connection: close');
    header('Content-Length: ' . $len);
    header('Content-Encoding: none');
    //header('Accept-Ranges: bytes');
  }
  
  private function dorequest($url) {
    //echo "'$url'<br>";
    $this->itemrequested = $this->finditem($url);
    if ($this->isredir) return;
    if ($this->itemrequested) {
      return $this->printcontent($this->itemrequested);
    } else {
      $this->notfound404();
    }
  }
  
  public function getidurl($id) {
    if (!isset($this->items[$id])) {
      $this->items[$id] = $this->db->getitem($id);
    }
    return $this->items[$id]['url'];
  }
  
  public function findurl($url) {
    if ($result = $this->db->finditem('url = '. dbquote($url))) return $result;
    return false;
  }
  
  public function urlexists($url) {
    return $this->db->findid('url = '. dbquote($url));
  }
  
  private function query($url) {
    if ($item = $this->db->getassoc('url = '. dbquote($url). ' limit 1')) {
      $this->items[$item['id']] = $item;
      return $item;
    }
    return false;
  }
  
  public function finditem($url) {
    if ($result = $this->query($url)) return $result;
    $srcurl = $url;
    if ($i = strpos($url, '?'))  $url = substr($url, 0, $i);
    if ('//' == substr($url, -2)) $this->redir(rtrim($url, '/') . '/');
    //extract page number
    if (preg_match('/(.*?)\/page\/(\d*?)\/?$/', $url, $m)) {
      if ('/' != substr($url, -1))  return $this->redir($url . '/');
      $url = $m[1];
      if ($url == '') $url = '/';
      $this->page = max(1, abs((int) $m[2]));
    }
    
    if ($result = $this->query($url)) {
      if (($this->page == 1) && ($result['type'] == 'normal') && ($srcurl != $result['url'])) return $this->redir($url);
      return $result;
    }
    $url = $url != rtrim($url, '/') ? rtrim($url, '/') : $url . '/';
    if ($result = $this->query($url)) {
      if ($this->page > 1) return $result;
      if ($result['type'] == 'normal') return $this->redir($url);
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
  
  private function getcachefile(array $item) {
      switch ($item['type']) {
        case 'normal':
        return  sprintf('%s-%d.php', $item['id'], $this->page);
        
        case 'usernormal':
        return sprintf('%s-page-%d-user-%d.php', $item['id'], $this->page, litepublisher::$options->user);

        case 'userget':
return sprintf('%s-page-%d-user%d-get-%s.php', $item['id'], $this->page, litepublisher::$options->user, md5($_SERVER['REQUEST_URI']));

        default: //get
return sprintf('%s-%d-%s.php', $item['id'], $this->page, md5($_SERVER['REQUEST_URI']));
     }
    }
  }
  
  private function include_file($fn) {
    if (tfilestorage::$memcache) {
if ($s = $this->loadfromcache($fn)) {
        eval('?>' . $s);
        return true;
    }
return false;
}
    
$filename = litepublisher::$paths->cache . $fn;
    if (file_exists($filename) &&
    ((filemtime ($filename) + litepublisher::$options->expiredcache - litepublisher::$options->filetime_offset) >= time())) {
      include($filename);
      return true;
    }
    
    return false;
  }
  
  private function  printcontent(array $item) {
    $options = litepublisher::$options;
    if ($this->cache_enabled) {
      if ($this->include_file($this->getcachefile($item))) return;
    }
    if (class_exists($item['class']))  {
      return $this->GenerateHTML($item);
    } else {
      //$this->deleteclass($item['class']);
      $this->notfound404();
    }
  }
  
  public function getidcontext($id) {
    $item = $this->getitem($id);
    return $this->getcontext($item);
  }
  
  public function getcontext(array $item) {
    $class = $item['class'];
    $parents = class_parents($class);
    if (in_array('titem', $parents)) {
      return call_user_func_array(array($class, 'i'), array($item['arg']));
    } else {
      return getinstance($class);
    }
  }
  
  protected function GenerateHTML(array $item) {
    $context = $this->getcontext($item);
    $this->context  = $context;
    
    //special handling for rss
    if (method_exists($context, 'request') && ($s = $context->request($item['arg']))) {
      switch ($s) {
        case 404: return $this->notfound404();
        case 403: return $this->forbidden();
      }
    } else {
      if ($this->isredir) return;
      $template = ttemplate::i();
      $s = $template->request($context);
    }
    eval('?>'. $s);
    if ($this->cache_enabled && $context->cache) {
$this->savetocache($this->getcachefile($item), $s);
    }
  }
  
  public function notfound404() {
    $redir = tredirector::i();
    if ($url  = $redir->get($this->url)) {
      return $this->redir($url);
    }
    
    $this->is404 = true;
    $this->printclasspage('tnotfound404');
  }
  
  private function printclasspage($classname) {
    $cachefile = $classname . '.php';
    if ($this->cache_enabled) {
      if ($this->include_file($cachefile)) return;
    }
    
    $obj = getinstance($classname);
    $Template = ttemplate::i();
    $s = $Template->request($obj);
    eval('?>'. $s);
    
    if ($this->cache_enabled && $obj->cache) {
      $this->savetocache($cachefile, $result);
    }
  }
  
  public function forbidden() {
    $this->is404 = true;
    $this->printclasspage('tforbidden');
  }
  
  public function addget($url, $class) {
    return $this->add($url, $class, null, 'get');
  }
  
  public function add($url, $class, $arg, $type = 'normal') {
    if (empty($url)) $this->error('Empty url to add');
    if (empty($class)) $this->error('Empty class name of adding url');
    if (!in_array($type, array('normal','get','tree', 'usernormal', 'userget'))) $this->error(sprintf('Invalid url type %s', $type));
    
    if ($item = $this->db->finditem('url = ' . dbquote($url))) $this->error(sprintf('Url "%s" already exists', $url));
    $item= array(
    'url' => $url,
    'class' => $class,
    'arg' => (string) $arg,
    'type' => $type
    );
    $item['id'] = $this->db->add($item);
    $this->items[$item['id']] = $item;
    return $item['id'];
  }
  
  public function delete($url) {
    $url = dbquote($url);
    if ($id = $this->db->findid('url = ' . $url)) {
      $this->db->iddelete($id);
    } else {
      return false;
    }
    
    $this->clearcache();
    $this->deleted($id);
    return true;
  }
  
  public function deleteclass($class) {
    if ($items =
    $this->db->getitems("class = '$class'")) {
      $this->db->delete("class = '$class'");
      foreach ($items as $item) $this->deleted($item);
    }
    $this->clearcache();
  }
  
  public function deleteitem($id) {
    if ($item = $this->db->getitem($id)) {
      $this->db->iddelete($id);
      $this->deleted($item);
    }
    $this->clearcache();
  }
  
  //for Archives
  public function GetClassUrls($class) {
    $res = $this->db->query("select url from $this->thistable where class = '$class'");
    return $this->db->res2id($res);
  }
  
  public function clearcache() {
if (tfilestorage::$memcache) {
$this->revision++;
$this->save();
} else {

    $path = litepublisher::$paths->cache;
    if ( $h = @opendir($path)) {
      while(FALSE !== ($filename = @readdir($h))) {
        if (($filename == '.') || ($filename == '..') || ($filename == '.svn')) continue;
        $file = $path. $filename;
        if (is_dir($file)) {
          tfiler::delete($file . DIRECTORY_SEPARATOR, true, true);
        } else {
          tfilestorage::delete($file);
        }
      }
      closedir($h);
    }
}    

    $this->onclearcache();
  }
  
  public function setexpired($id) {
    tfiler::deletemask(litepublisher::$paths->cache . "$id-*.php");
  }
  
  public function setexpiredcurrent() {
$this->removefromcache($this->getcachefile($this->itemrequested));
  }
  
  public function getcachename($name, $id) {
    return litepublisher::$paths->cache. "$name-$id.php";
  }
  
  public function expiredname($name, $id) {
    tfiler::deletedirmask(litepublisher::$paths->cache, "*$name-$id.php");
  }
  
  public function expiredclass($class) {
    $items = $this->db->idselect("class = '$class'");
    foreach ($items as $id) $this->setexpired($id);
  }

public function savetocache($filename, $data) {
if (tfilestorage::$memcache) {
tfilestorage::$memcache->set(litepublisher::$domain . ':cache:' . $filename, 
serialize(array(
'revision' => $this->revision,
'time' => time(),
'data' => $data
)), false, 3600);
} else {
$fn = litepublisher::$paths->cache . $filename;
    file_put_contents($fn, $data);
    @chmod($fn, 0666);
}
}

public function loadfromcache($filename) {
if (tfilestorage::$memcache) {
$k = litepublisher::$domain . ':cache:' . $filename;
if ($s = tfilestorage::$memcache->get($k)) {
$a = unserialize($s);
if ($a['revision'] == $this->revision) {
return $a['data'];
} else {
tfilestorage::$memcache->delete($k);
}
}
return false;
} else {
$fn = litepublisher::$paths->cache . $filename;
    if (file_exists($fn)) return  file_get_contents($fn);
return false;
}
}

public function removefromcache($filename) {
if (tfilestorage::$memcache) {
tfilestorage::$memcache->delete(litepublisher::$domain . ':cache:' . $filename);
} else {
$fn = litepublisher::$paths->cache . $filename;
if (file_exists($fn)) unlink($fn);
}
}

public function incache($filename) {
if (tfilestorage::$memcache) {
return !!tfilestorage::$memcache->get(litepublisher::$domain . ':cache:' . $filename);
} else {
return file_exists(litepublisher::$paths->cache . $filename);
}
}

  public function addredir($from, $to) {
    if ($from == $to) return;
    $Redir = tredirector::i();
    $Redir->add($from, $to);
  }
  
  public static function unsub($obj) {
    $self = self::i();
    $self->lock();
    $self->unbind($obj);
    $self->deleteclass(get_class($obj));
    $self->unlock();
  }
  
  public function setonclose(array $a) {
    if (count($a) == 0) return;
    $this->close_events[] = $a;
  }
  
  public function onclose() {
    $this->setonclose(func_get_args());
  }
  
  private function call_close_events() {
    foreach ($this->close_events as $a) {
      try {
        $c = array_shift($a);
        if (!is_callable($c)) {
          $c = array($c, array_shift($a));
        }
        call_user_func_array($c, $a);
      } catch (Exception $e) {
        litepublisher::$options->handexception($e);
      }
    }
    $this->close_events = array();
  }
  
  protected function close() {
    $this->call_close_events();
    if (tfilestorage::$memcache) {
      $memcache = tfilestorage::$memcache;
      $k =litepublisher::$domain . ':lastpinged';
      $lastpinged = $memcache->get($k);
      if (!$lastpinged  || (time() > $lastpinged  + 3600)) {
        $memcache->set($k, time(), false, 3600);
        tcron::pingonshutdown();
      }else {
        $k =litepublisher::$domain . ':singlepinged';
        $singlepinged = $memcache->get($k);
        if ($singlepinged && (time() > $singlepinged  + 300)) {
          $memcache->delete($k);
          tcron::pingonshutdown();
        }
      }
    } elseif (time() > litepublisher::$options->crontime + 3600) {
      litepublisher::$options->crontime = time();
      tcron::pingonshutdown();
    }
  }
  
  public function redir($url, $status = 301) {
    litepublisher::$options->savemodified();
    $this->isredir = true;
    
    switch ($status) {
      case 301:
      header('HTTP/1.1 301 Moved Permanently', true, 301);
      break;
      
      case 302:
      header('HTTP/1.1 302 Found', true, 302);
      break;
      
      case 307:
      header('HTTP/1.1 307 Temporary Redirect', true, 307);
      break;
    }
    
    if (!strbegin($url, 'http://') && !strbegin($url, 'https://')) $url = litepublisher::$site->url . $url;
    header('Location: ' . $url);
  }
  
  public function seturlvalue($url, $name, $value) {
    if ($id = $this->urlexists($url)) {
      $this->setvalue($id, $name, $value);
    }
  }
  
  public function setidurl($id, $url) {
    $this->db->setvalue($id, 'url', $url);
    if (isset($this->items[$id])) $this->items[$id]['url'] = $url;
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
    header('X-Pingback: ' . litepublisher::$site->url . '/rpc.xml');
  }
  
  public static function sendxml() {
    header('Content-Type: text/xml; charset=utf-8');
    header('Last-Modified: ' . date('r'));
    header('X-Pingback: ' . litepublisher::$site->url . '/rpc.xml');
    echo '<?xml version="1.0" encoding="utf-8" ?>';
  }
  
}//class