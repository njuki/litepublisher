<?php

class tusers extends TItems {

  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'users';
$this->table = 'users';
$this->autoid = 1;
}

public function add($group, $login,$password, $name, $email, $url) {
global $options;
if ($this->loginexists($login)) return false;
$groups = tusergroups::instance();
if (!($gid = $groups->groupid($group)) return false;
$password = md5("$login:$options->realm:$password");
$item = array(
'group' => $gid,
'login' => $login,
'password' => $password,
'cookie' =>  md5(mt_rand() . secret. microtime()),
'expired' => 0,
'name' => $name,
'email' => $email
'url' => $url
);

if (dbversion) {
return $this->db->insertassoc($item);
} else {
$this->items[++$this->autoid] = $item;
$this->save();
return $this->autoid;
}
}

ppublic function loginexists($login) {
global $options;
if ($login == $options->login) return 1;
if (dbversion) {
return $this->db->findid('login = '. dbquote($login));
} else {
foreach ($this->items as $id => $item) {
if ($login == $item['login']) return true;
}
return false;
}
}

public function getpassword($id) {
global $options;
return $id == 1 ? $options->password : $this->getvalue($id, 'password');

public function auth($login,$password) {
global $options;
$password = md5("$login:$options->realm:$password");
if (dbversion) {
$login = dbquote($login);
return $this->db->findid("login = $login and password = '$password'");
} else {
foreach ($this->items as $id => $item) {
if ($login == $item['login']) && ($password = $item['password'])) return $id;
}
}
return  false;
}

public function getgroupname($id) {
$groups = tusergroups::instance();
return $groups->items[$this->items[$id]['group']]['name'];
}


public function clearcookie($id) {
$this->setcookies($id, '', 0);
}

public function setcookies($id, $cookie, $xpired) {
if (dbversion) {
$this->db->updateassoc(array(
'id' => $id',
'cookie' => $cookie,
'expired' => $expired
));
} else {
$this->items[$id]['cookie'] = $cookie;
$this->items[$id]['expired'] = $expired;
$this->save();
}
}
}//class
?>