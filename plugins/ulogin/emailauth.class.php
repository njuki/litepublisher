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
    $email = trim($args['email']);
    $password = trim($args['password']);
    if (empty($email) || empty($password)) return $this->error('Invalid data', 403);
    if (!litepublisher::$options->auth($email, $password)) return $this->error('Invalid password', 403);

    $expired = time() + 31536000;
    $cookie = md5uniq();
    litepublisher::$options->setcookies($cookie, $expired);

    return array(
    'id' => litepublisher:$options->user,
    'pass' => $cookie,
    'regservice' => 'email',
'adminflag' => litepublisher::$options->ingroup('admin') ? 'true' : false,
    );
}

  public function email_reg(array $args) {
return tadminreguser ::i()->reguser($args['email'], $args['name']);
}

  public function email_lostpass(array $args) {
}

}//class