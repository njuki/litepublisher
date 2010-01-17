<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tusers extends titems {
  
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
    if (!($gid = $groups->groupid($group))) return false;
    $password = md5("$login:$options->realm:$password");
    $item = array(
    'group' => $gid,
    'login' => $login,
    'password' => $password,
    'cookie' =>  md5uniq(),
    'expired' => 0,
    'name' => $name,
    'email' => $email,
    'url' => $url
    );
    
    if ($this->dbversion) {
      return $this->db->add($item);
    } else {
      $this->items[++$this->autoid] = $item;
      $this->save();
      return $this->autoid;
    }
  }
  
  public function loginexists($login) {
    global $options;
    if ($login == $options->login) return 1;
    if ($this->dbversion) {
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
  }
  
  public function auth($login,$password) {
    global $options;
    $password = md5("$login:$options->realm:$password");
    if ($this->dbversion) {
      $login = dbquote($login);
      return $this->db->findid("login = $login and password = '$password'");
    } else {
      foreach ($this->items as $id => $item) {
        if (($login == $item['login']) && ($password = $item['password'])) return $id;
      }
    }
    return  false;
  }

public function authcookie($cookie) {
if (empty($cookie)) return false;
foreach ($this->items as $id => $item) {
if ($cookie == $item['cookie']) {
if ($item['expired'] < time()) return  false;
return $id;
}
}
return false;
}
  
  public function getgroupname($id) {
    $groups = tusergroups::instance();
    return $groups->items[$this->items[$id]['group']]['name'];
  }
  
    public function clearcookie($id) {
    $this->setcookies($id, '', 0);
  }
  
  public function setcookies($id, $cookie, $expired) {
    if ($this->dbversion) {
      $this->db->updateassoc(array(
      'id' => $id,
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