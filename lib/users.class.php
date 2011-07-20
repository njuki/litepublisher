<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tusers extends titems {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = dbversion;
    parent::create();
    $this->basename = 'users';
    $this->table = 'users';
    $this->autoid = 1;
  }
  
  public function add($group, $login,$password, $name, $email, $website) {
    if ($this->loginexists($login)) return false;
    $groups = tusergroups::instance();
    if (is_numeric($group)) {
      $gid = (int) $group;
      if (!$groups->itemexists($gid)) return false;
    } else {
      if (!($gid = $groups->groupid($group))) return false;
    }
    if ($password == '') $password = md5uniq();
    $password = basemd5(sprintf('%s:%s:%s', $login,  litepublisher::$options->realm, $password));
    
    $item = array(
    'login' => $login,
    'password' => $password,
    'cookie' =>  md5uniq(),
    'expired' => sqldate(),
    'registered' => sqldate(),
    'gid' => $gid,
    'trust' => 0,
    'status' => 'wait',
    'name' => $name,
    'email' => $email,
    'website' => $website,
    'ip' => '',
    'avatar' => 0,
'idurl' => 0
    );
    
    $id = $this->dbversion ? $this->db->add($item) : ++$this->autoid;
    $this->items[$id] = $item;
    if ($this->dbversion) $this->save();
    $this->added($id);
    return $id;
  }
  
  public function edit($id, array $values) {
    if (!$this->itemexists($id)) return false;
    $item = $this->getitem($id);
    
    $group = $values['group'];
    $groups = tusergroups::instance();
    if (is_numeric($group)) {
      $gid = (int) $group;
      if (!$groups->itemexists($gid)) return false;
    } else {
      if (!($gid = $groups->groupid($group))) return false;
    }
    
    $item['gid'] = $gid;
    
    foreach ($item as $k => $v) {
      if (!isset($values[$k])) continue;
      switch ($k) {
        case 'password':
        if ($values['password'] != '') {
          $item['password'] = basemd5(sprintf('%s:%s:%s', $values['login'],  litepublisher::$options->realm, $values['password']));
        }
        break;
        
        default:
        $item[$k] = trim($values[$k]);
      }
    }
    
    $this->items[$id] = $item;
    $item['id'] = $id;
    if ($this->dbversion) {
      $this->db->updateassoc($item);
    } else {
      $this->save();
    }
    return true;
  }
  
  public function loginexists($login) {
    if ($login == litepublisher::$options->login) return 1;
    if ($this->dbversion) {
      return $this->db->findid('login = '. dbquote($login));
    } else {
      foreach ($this->items as $id => $item) {
        if ($login == $item['login']) return true;
      }
      return false;
    }
  }
  
  public function emailexists($email) {
    if ($email == litepublisher::$options->email) return 1;
    if ($this->dbversion) {
      return $this->db->findid('email = '. dbquote($email));
    } else {
      foreach ($this->items as $id => $item) {
        if ($email == $item['email']) return true;
      }
      return false;
    }
  }
  
  public function getpassword($id) {
    return $id == 1 ? litepublisher::$options->password : $this->getvalue($id, 'password');
  }
  
  public function changepassword($id, $password) {
    $item = $this->getitem($id);
    $this->setvalue($id, 'password', basemd5(sprintf('%s:%s:%s', $item['login'],  litepublisher::$options->realm, $password)));
  }
  
  public function auth($login,$password) {
    $password = basemd5(sprintf('%s:%s:%s', $login,  litepublisher::$options->realm, $password));
    if ($this->dbversion) {
      $login = dbquote($login);
      if (($a = $this->select("login = $login and password = '$password'", 'limit 1')) && (count($a) > 0)) {
        $item = $this->getitem($a[0]);
        if ($item['status'] == 'wait') $this->db->setvalue($item['id'], 'status', 'approved');
        return (int) $item['id'];
      }
    } else {
      foreach ($this->items as $id => $item) {
        if (($login == $item['login']) && ($password = $item['password'])) {
          if ($item['status'] == 'wait') {
            $this->items[$id]['status'] = 'approved';
            $this->save();
          }
          return $id;
        }
      }
    }
    return  false;
  }
  
  public function authcookie($cookie) {
    $cookie = (string) $cookie;
    if (empty($cookie)) return false;
    $cookie = basemd5( $cookie . litepublisher::$secret);
    if ($this->dbversion) {
      if (($a = $this->select("cookie = '$cookie'", 'limit 1')) && (count($a) > 0)) {
        $item = $this->getitem($a[0]);
        if (strtotime($item['expired']) < time()) return  false;
        return (int) $item['id'];
      }
    } else {
      foreach ($this->items as $id => $item) {
        if ($cookie == $item['cookie']) {
          if (strtotime($item['expired']) < time()) return  false;
          return $id;
        }
      }
    }
    return false;
  }
  
  public function getgroupname($id) {
    $item = $this->getitem($id);
    $groups = tusergroups::instance();
    return $groups->items[$item['gid']]['name'];
  }
  
  public function clearcookie($id) {
    $this->setcookie($id, '', 0);
  }
  
  public function setcookie($id, $cookie, $expired) {
    if ($cookie != '') $cookie = basemd5($cookie . litepublisher::$secret);
    $expired = sqldate($expired);
    if (isset($this->items[$id])) {
      $this->items[$id]['cookie'] = $cookie;
      $this->items[$id]['expired'] = $expired;
    }
    
    if ($this->dbversion) {
      $this->db->updateassoc(array(
      'id' => $id,
      'cookie' => $cookie,
      'expired' => $expired
      ));
    } else {
      $this->save();
    }
  }
  
  public function request($arg) {
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 1;
    if (!$this->itemexists($id)) return 404;
    $item = $this->getitem($id);
    $website = $item['website'];
    if (!strpos($website, '.')) $website = litepublisher::$site->website . litepublisher::$site->home;
    if (!strbegin($website, 'http://')) $website = 'http://' . $website;
    return "<?php turlmap::redir('$website');";
  }
  
    public function optimize() {
    if ($this->dbversion) {
      $time = sqldate(strtotime('-1 day'));
      $this->db->delete("status = 'wait' and registered < '$time'");
    } else {
      $time = strtotime('-1 day');
      $deleted = false;
      foreach ($this->items as $id => $item) {
        if (($item['status'] == 'wait') && ($item['registered'] < $time)) {
          unset($this->items[$id]);
          $deleted = true;
        }
      }
      if ($deleted) $this->save();
    }
  }
  
}//class
?>