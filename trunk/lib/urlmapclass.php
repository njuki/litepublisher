<?php

class turlmap extends TItems {
  public $host;
  public $url;
  public $urlid;
  public $page;
  public $uripath;
  public $is404;
  public $admin;
  public $mobile;
  
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
  $this->uripath = array();
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
  
  protected function DoRequest($url) {
    if ($item = $this->finditem($url)) return $this->PrintContent($item);
    $this->NotFound404();
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
$result['arg'] = substr($url, $i +1);
return $result;
}
$j = - (strlen($url) - $i + 1);
}

return false;    
  }
  
  protected function  PrintContent(array $item) {
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
    
    if (class_exists($item['class']))  return $this->GenerateHTML($item);

        $this->DeleteClass($item['class']);
$this->NotFound404();
  }
  
  protected function GenerateHTML(array $item) {
    global $options, $paths, $template;
    $obj = getinstance($item['class']);
    //special handling for rss
    if (method_exists($obj, 'request') && ($s = $obj->request($item['arg']))) {
      if ($s == 404) return $this->NotFound404();
    } else {
      $template = ttemplate::instance();
      $s = $template->request($obj);
    }
    eval('?>'. $s);
    if ($options->CacheEnabled && $obj->CacheEnabled) {
  $CacheFileName = "{$paths['cache']}{$item['id']}-$this->page.php";
      file_put_contents($CacheFileName, $s);
      @chmod($CacheFileName, 0666);
    }
  }
  
  public function NotFound404() {
    $redir = TRedirector ::instance();
    if (isset($redir->items[$this->url])) {
      return $this->redir301($redir->items[$this->url]);
    }
    
    $this->is404 = true;
    $obj = TNotFound404::instance();
    $Template = ttemplate::instance();
    $s = &$Template->request($obj);
    eval('?>'. $s);
  }
  
  public function add($url, $class, $arg, $type = 'nornal') {
if (dbversion) {
$item= array(
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
if (isset($this->items[$url])) {
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
  
  public function addredir($from, $to) {
    if ($from == $to) return;
    $Redir = &TRedirector::instance();
    $Redir->add($from, $to);
  }
  
  public static function unsub(&$obj) {
    $self = self::instance();
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
      register_shutdown_function('tcron::selfping');
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