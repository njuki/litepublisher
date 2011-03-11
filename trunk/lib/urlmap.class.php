<?php
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
  public $onclose;
  
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
    $this->onclose = array();
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
    $this->close();
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
  
  private function call_close_events() {
    foreach ($this->onclose as $event) {
      try {
        call_user_func($event);
      } catch (Exception $e) {
        litepublisher::$options->handexception($e);
      }
    }
    $this->onclose = array();
  }
  
  protected function close() {
    $this->call_close_events();
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
    header('X-Pingback: ' . litepublisher::$site->url . '/rpc.xml');
  }
  
  public static function sendxml() {
    header('Content-Type: text/xml; charset=utf-8');
    header('Last-Modified: ' . date('r'));
    header('X-Pingback: ' . litepublisher::$site->url . '/rpc.xml');
    echo '<?xml version="1.0" encoding="utf-8" ?>';
  }
  
}//class

?>