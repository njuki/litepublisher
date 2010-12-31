<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tauthdigest extends tevents {
  public $stale;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function Create() {
    parent::create();
    $this->basename = 'authdigest';
    $this->data['nonce'] = '';
    $this->data['time'] = 0;
    $this->data['xxxcheck'] = true;
    $this->data['logoutneeded'] = false;
    $this->stale = false;
  }
  
  public function afterload() {
    parent::afterload();
    if ($this->time + 600 < time()) $this->newnonce();
  }
  
  private function newnonce() {
    $this->data['nonce'] = md5uniq();
    $this->data['time'] = time();
    $this->save();
  }
  
  private function GetDigestHeader() {
    if ($uri = preg_replace_callback('/,uri="(.*?)"/',
    create_function('$matches', 'return \',uri="\' . urlencode($matches[1]) . \'"\';'),
    $_SERVER["QUERY_STRING"])) {
      parse_str($uri, $_GET);
      parse_str($uri, $_REQUEST);
    }
    
    if (function_exists('apache_request_headers') && ini_get('safe_mode') == false) {
      $arh = apache_request_headers();
      return  isset($arh['Authorization']) ? $arh['Authorization'] : null;
    } elseif (isset($_SERVER['PHP_AUTH_DIGEST'])) {
      return $_SERVER['PHP_AUTH_DIGEST'];
    } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
      return $_SERVER['HTTP_AUTHORIZATION'];
    } elseif (isset($_ENV['PHP_AUTH_DIGEST'])) {
      return $_ENV['PHP_AUTH_DIGEST'];
    } elseif (isset($_SERVER['Authorization'])) {
      return $_SERVER['Authorization'];
    } elseif (isset($_REQUEST['HTTP_AUTHORIZATION'])) {
      return stripslashes(urldecode($_REQUEST['HTTP_AUTHORIZATION']));
    }
    return null;
  }
  
  public function auth() {
    if ($this->logoutneeded) {
      $this->logoutneeded = false;
      $this->save();
      return false;
    }
    
    if ($this->nonce == '') $this->newnonce();
    if ($digest  = $this->GetDigestHeader()) {
      $digest  = substr($digest,0,7) == 'Digest ' ?  substr($digest, strpos($digest, ' ') + 1) : $digest ;
      preg_match_all('/(\w+)=(?:"([^"]+)"|([^\s,]+))/', $digest, $mtx, PREG_SET_ORDER);
      $hdr = array();
      foreach ($mtx as $m) $hdr[$m[1]] = $m[2] ? $m[2] : $m[3];
      if (count($hdr) == 0) return false;
      if ($this->nonce != $hdr['nonce'])  {
        $this->stale  = true;
        return false;
      }
      $users = tusers::instance();
      if (!(litepublisher::$options->user  =$users->loginexists($hdr['username']))) return false;
      litepublisher::$options->updategroup();
      $a1 = strtolower(litepublisher::$options->password);
      $a2 = md5($_SERVER['REQUEST_METHOD'] .':' . $hdr['uri']);
      return $hdr['response'] == md5("$a1:$this->nonce:$a2");
    }
    return false;
  }
  
  public function Headers() {
    $protocol = $_SERVER["SERVER_PROTOCOL"];
    if ( ('HTTP/1.1' != $protocol) && ('HTTP/1.0' != $protocol) ) $protocol = 'HTTP/1.0';
    $stale = $this->stale ? 'true' : 'false';
    
    $result = "<?php
    @header('WWW-Authenticate: Digest realm=\"litepublisher::$options->realm\", nonce=\"$this->nonce\", stale=\"$stale\"');
    @header('$protocol 401 Unauthorized', true, 401);
    echo '401 Unauthorized';
    ?>";
    return $result;
  }
  
  public function isattack() {
    if (isset($_GET['ref'])) {
      $ref = $_GET['ref'];
      $url = $_SERVER['REQUEST_URI'];
      $url = substr($url, 0, strpos($url, '&ref='));
      //var_dump($url);
      if ($ref == md5(litepublisher::$secret . litepublisher::$site->url . $url)) return false;
    }
    
    $host = '';
    if (!empty($_SERVER['HTTP_REFERER'])) {
      $p = parse_url($_SERVER['HTTP_REFERER']);
      $host = $p['host'];
    }
    return $host != $_SERVER['HTTP_HOST'];
  }
  
  public function checkattack() {
    if ($this->xxxcheck  && $this->isattack()) {
      tlocal::loadlang('admin');
      if ($_POST) {
        die(tlocal::$data['login']['xxxattack']);
      }
      if ($_GET) {
        die(tlocal::$data['login']['confirmxxxattack'] .
        sprintf(' <a href="%1$s">%1$s</a>', $_SERVER['REQUEST_URI']));
      }
    }
    return false;
  }
  
  public function logout() {
    if (litepublisher::$options->cookieenabled) {
      $this->setcookies('', 0);
    }
    $this->lock();
    $this->newnonce();
    $this->logoutneeded = true;
    $this->unlock();
  }
  
  public function setcookies($cookie, $expired) {
    setcookie('admin', $cookie, $expired, litepublisher::$site->subdir . '/', false);
    if (litepublisher::$options->user == 1) {
      litepublisher::$options->setcookie($cookie);
      litepublisher::$options->cookieexpired = $expired;
    } else {
      $users = tusers::instance();
      $users->setcookie(litepublisher::$options->user, $cookie, $expired);
    }
    
    
  }
  
}//class

?>