<?php

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
    $this->data['cookie'] = '';
    $this->data['cookieenabled'] = false;
    $this->data['cookieexpired'] = 0;
    $this->data['xxxcheck'] = true;
    $this->stale = false;
  }
  
  public function afterload() {
    parent::afterload();
    if ($this->time + 600 < time()) $this->newnonce();
  }
  
  private function newnonce() {
    $this->data['nonce'] = md5(mt_rand() . secret. microtime());
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
    global $options;
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
if (!($options->user  =$users->loginexists($hdr['username']))) return false;
$options->updategroup();
      $a1 = strtolower($options->password);
      $a2 = md5($_SERVER['REQUEST_METHOD'] .':' . $hdr['uri']);
      return $hdr['response'] == md5("$a1:$this->nonce:$a2");
    }
    return false;
  }
  
  public function Headers() {
    global $options;
    $protocol = $_SERVER["SERVER_PROTOCOL"];
    if ( ('HTTP/1.1' != $protocol) && ('HTTP/1.0' != $protocol) ) $protocol = 'HTTP/1.0';
    $stale = $this->stale ? 'true' : 'false';
    
    $result = "<?php
    @header('WWW-Authenticate: Digest realm=\"$options->realm\", nonce=\"$this->nonce\", stale=\"$stale\"');
    @header('$protocol 401 Unauthorized', true, 401);
    echo '401 Unauthorized';
    ?>";
    return $result;
  }
  
public function isattack() {
$host = '';
        if (!empty($_SERVER['HTTP_REFERER'])) {
          $p = parse_url($_SERVER['HTTP_REFERER']);
          $host = $p['host'];
        }

return $host == $_SERVER['HTTP_HOST'] ;
}

public function checkattack() {
      if ($this->xxxcheck  && $this->isattack()) {
          if ($_POST) die('<b><font color="red">Achtung! XSS attack!</font></b>');
      if ($_GET)  die("<b><font color=\"maroon\">Achtung! XSS attack?</font></b><br>Confirm transition: <a href=\"{$_SERVER['REQUEST_URI']}\">{$_SERVER['REQUEST_URI']}</a>");
}
return false;
}

public function authcookie() {
global $options;
      if (empty($_COOKIE['admin']) ) return false;
if ($auth->cookie == $_COOKIE['admin']) {
if ($auth->cookieexpired < time()) return  false;
$options->user = 1;
$options->group = 'admin';
return true;
}

$users = tusers::instance();
if($options->user = $users->IndexOf('cookie',$_COOKIE['admin'])) {
if ($users->getvalue($options->user, 'cookieexpired') < time()) return  false;
$options->updategroup();
return;
}
return false;
}

public function logout() {
global $options;
if ($this->cookieenabled) {
$this->setcookies('', 0);
} else {
$this->newnonce();
}
}

public function setcookies($cookie, $expired) {
global $options;
if ($options->user == 1) {
$this->cookie = $cookie;
$this->expired = $expired;
$this->save();
} else {
$users = tusers::instance();
$users->setcookie($options->user, $cookie, $expired);
}
}

}//class

?>