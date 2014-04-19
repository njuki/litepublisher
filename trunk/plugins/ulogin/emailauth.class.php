<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class emailauth extends tplugin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function email_login(array $args) {
    if (!isset($args['email']) || !isset($args['password'])) return $this->error('Invalid data', 403);
    $email = strtolower(trim($args['email']));
    $password = trim($args['password']);
    if (empty($email) || empty($password)) return $this->error('Invalid data', 403);
    if (!litepublisher::$options->auth($email, $password)) {
if (!$this->confirm_reg($email, $password) && !$this->confirm_restore($email, $password)) {
return array(
'error' => tlocal::i()->errpassword
);
}
}

    $expired = time() + 31536000;
    $cookie = md5uniq();
    litepublisher::$options->setcookies($cookie, $expired);

    return array(
    'id' => litepublisher::$options->user,
    'pass' => $cookie,
    'regservice' => 'email',
'adminflag' => litepublisher::$options->ingroup('admin') ? 'true' : false,
    );
}

  public function email_reg(array $args) {
    if (!litepublisher::$options->usersenabled || !litepublisher::$options->reguser) return array(
'error' => tlocal::admin('users')->regdisabled
);

try {
return tadminreguser ::i()->reguser($args['email'], $args['name']);
    } catch (Exception $e) {
return array(
'error' => $e->getMessage()
);
}
}

  public function email_lostpass(array $args) {
try {
return tadminpassword::i()->restore($args['email']);
    } catch (Exception $e) {
return array(
'error' => $e->getMessage()
);
}
}

public function confirm_reg($email, $password) {
      tsession::start('reguser-' . md5($email));
      if (!isset($_SESSION['email']) || ($email != $_SESSION['email']) || ($password != $_SESSION['password'])) {
        if (isset($_SESSION['email'])) {
session_write_close();
} else {
session_destroy();
}
return false;
      }

      $users = tusers::i();
      $id = $users->add(array(
      'password' => $password,
      'name' => $_SESSION['name'],
      'email' => $email
      ));
      
      session_destroy();

      if ($id) {
        litepublisher::$options->user = $id;
        litepublisher::$options->updategroup();
}

return $id;
}

public function confirm_restore($email, $password) {
      tsession::start('password-restore-' .md5($email));
      if (!isset($_SESSION['email']) || ($email != $_SESSION['email']) || ($password != $_SESSION['password'])) {
        if (isset($_SESSION['email'])) {
session_write_close();
} else {
session_destroy();
}
return false;
}

      session_destroy();
    if ($email == strtolower(trim(litepublisher::$options->email))) {
          litepublisher::$options->changepassword($password);
return 1;
        } else {
   $users = tusers::i();
if ($id = $users->emailexists($email)) $users->changepassword($id, $password);
return $id;
        }
}

}//class