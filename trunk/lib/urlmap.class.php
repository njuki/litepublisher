<?php
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
  public $cachefilename;
  public $argtree;
  public $is404;
  public $admin;
  public $mobile;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = dbversion;
    parent::create();
    $this->table = 'urlmap';
    $this->basename = 'urlmap';
    $this->addevents('beforerequest', 'afterrequest', 'CacheExpired');
    $this->is404 = false;
    $this->admin = false;
    $this->mobile= false;
    $this->cachefilename = false;
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
      if ($res = $this->db->select('url = '. dbquote($url). ' limit 1')) {
        $item = $res->fetch(PDO::FETCH_ASSOC);
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
      if ($result['type'] == 'normal') return $this->redir301($url);
      return $result;
    }
    
    $this->uripath = explode('/', trim($url, '/'));
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
    if (!$this->cachefilename) {
      if ($item['type'] == 'normal') {
        $this->cachefilename =  sprintf('%s-%d.php', $item['id'], $this->page);
      } else {
        $this->cachefilename = sprintf('%s-%d-%s.php', $item['id'], $this->page, md5($this->url));
      }
    }
    return $paths['cache'] . $this->cachefilename;
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
    return $paths['cache']. "$name-$id.php";
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

?>