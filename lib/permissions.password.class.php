<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tpermpassword extends tperm {

protected function create() {
parent::create();
$this->adminclass = 'tadminpermpassword';
$this->data['password'] = '';
$this->data['login'] = '';
}

public function getheader($obj) {
if ($this->password == '') return '';
return sprintf('<?php %s::i(%d)->auth(); ?>', get_class($this), $this->id));
}

public function getcookiename() {
return 'permpassword_' .$this->id;
}

public function setpassword($p) {
$p = trim($p);
if ($p == '') return false;
$this->data['login'] = md5uniq();
$this->data['password'] = md5($this->login . litepublisher::$secret . $p);
$this->save();
}

public function checkpassword($p) {
if ($this->password != md5($this->login . litepublisher::$secret . $p)) return false;
    $login = md5(mt_rand() . litepublisher::$secret. microtime());
$password = md5($login . litepublisher::$secret . $this->password);
$cookie = $login . '.' . $password;
    $expired = isset($_POST['remember']) ? time() + 1210000 : time() + 8*3600;

    setcookie($this->getcookiename(), $cookie, $expired, litepublisher::$site->subdir . '/', false);
return true;
}

public function auth($id) {
if (litepublisher::$options->group == 'admin') return true;
$cookiename = $this->getcookiename();
$cookie = isset($_COOKIE[$cookiename]) ? $_COOKIE[$cookiename] : '';
if (($cookie == '') || strpos($cookie, '.')) return $this->redir();
list($login, $password) = explode('.', $cookie);
if ($password == md5($login . litepublisher::$secret . $this->password)) return true;

return $this->redir();
}

public function redir() {
$url = litepublisher::$site->url . '/check-password.php' . litepublisher::$site->q;
$url .= "idperm=$this->id&backurl=" . urlencode(litepublisher::$urlmap->url);
    litepublisher::$options->savemodified();
        header('HTTP/1.1 307 Temporary Redirect', true, 307);
    header('Location: ' . $url);

    if (ob_get_level()) ob_end_flush ();
    exit();
}

}//class