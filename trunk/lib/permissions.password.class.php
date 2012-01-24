<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tpermpassword extends tperm {

public function getheader($obj) {
if (isset($obj->password) && ($p = $obj->password)) {
return sprintf('<?php if (!%s::auth(\'%s\')) return; ?>', get_class($this), $this->encryptpassword($p));
}
}

public function encryptpassword($p) {
return md5(litepublisher::$urlmap->url . litepublisher::$secret . $p);
}

protected function getpasswordcookie() {
return basemd5('post_' . $this->id .litepublisher::$secret . $this->password);
}

public static function auth($p) {
if (litepublisher::$options->group == 'admin') return;
$cookiename = 'singlepwd_' . litepublisher::$urlmap->itemrequested['id'];
$cookie = isset($_COOKIE[$cookiename]) ? $_COOKIE[$cookiename] : '';
if ($cookie != '') {
list($login, $password) = explode('.', $cookie);
if ($password == md5($login . litepublisher::$secret . $p)) return;
}
return self::redir('type=single&backurl=' . urlencode(litepublisher::$urlmap->url));
}

public static function redir($params) {
    litepublisher::$options->savemodified();
$url = litepublisher::$site->url . '/send-post-password.php' . litepublisher::$site->q . $params;
    if ( php_sapi_name() != 'cgi-fcgi' ) {
      $protocol = $_SERVER["SERVER_PROTOCOL"];
      if ( ('HTTP/1.1' != $protocol) && ('HTTP/1.0' != $protocol) ) $protocol = 'HTTP/1.0';
      header( "$protocol 307 Temporary Redirect", true, 307);
    }
    
    header('Location: ' . $url);
    if (ob_get_level()) ob_end_flush ();
    exit();
  }
  
}//class
