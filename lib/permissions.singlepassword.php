<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tsinglepassword extends tperm {

public function getheader($obj) {
if (isset($obj->password) && ($p = $obj->password)) {
return sprintf('<?php if (!%s::auth(\'%s\')) return; ?>', __class__, self::encryptpassword($p));
}
}

public static function encryptpassword($p) {
return md5(litepublisher::$urlmap->itemrequested['id'] . litepublisher::$secret . $p);
}


public static function setcookie($p) {
$cookiename = 'singlepwd_' . litepublisher::$urlmap->itemrequested['id'];
$cookie = 
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

