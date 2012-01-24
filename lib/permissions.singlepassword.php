<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tsinglepassword extends tperm {
private $password;

public function getheader($obj) {
if (isset($obj->password) && ($p = $obj->password)) {
return sprintf('<?php if (!%s::auth(%d, \'%s\')) return; ?>', get_class($this), $this->id, self::encryptpassword($p));
}
}

public static function encryptpassword($p) {
return md5(litepublisher::$urlmap->itemrequested['id'] . litepublisher::$secret . $p);
}

public static function setcookie($p) {
$cookiename = 'singlepwd_' . litepublisher::$urlmap->itemrequested['id'];
$cookie = 
}

public static function auth($id, $p) {
if (litepublisher::$options->group == 'admin') return true;
$cookiename = 'singlepwd_' . litepublisher::$urlmap->itemrequested['id'];
$cookie = isset($_COOKIE[$cookiename]) ? $_COOKIE[$cookiename] : '';
if (($cookie != '') && strpos($cookie, '.')) {
list($login, $password) = explode('.', $cookie);
if ($password == md5($login . litepublisher::$secret . $p)) return ttrue;
}

$self = self::i($id);
return $self->getform($p);
}

public function getform($p) {
$this->password = $p;
$page = tpasswordpage::i();
$page->perm = $this;
$page->request();

      $html  = ttemplate::i()->request($page);

    eval('?>'. $s);
}

}//class
