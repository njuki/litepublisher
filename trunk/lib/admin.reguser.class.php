<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminreguser extends tadminform {
  private $registered;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->section = 'users';
    $this->registered = false;
  }
  
  public function request($arg) {
    if (!litepublisher::$options->usersenabled || !litepublisher::$options->reguser) return 403;
    return parent::request($arg);
  }
  
  public function gettitle() {
    return tlocal::$data['users']['adduser'];
  }
  
  public function getlogged() {
    if (litepublisher::$options->cookieenabled) {
      return litepublisher::$options->authcookie();
    } else {
      $auth = tauthdigest::instance();
      return $auth->auth();
    }
  }
  
  public function getcontent() {
    $html = $this->html;
    if ($this->registered) return $html->waitconfirm();
    if ($this->logged) return $html->logged();
    
    $args = targs::instance();
    foreach (array('login', 'email', 'name', 'url') as $name) {
      $args->$name = isset($_POST[$name]) ? $_POST[$name] : '';
    }
    return $html->regform($args);
  }
  
  public function processform() {
    extract($_POST);
    if (!tcontentfilter::ValidateEmail($email)) return '<p><strong>' .  tlocal::$data['comment']['invalidemail'] . "</strong></p>\n";
    $users = tusers::instance();
    if ($users->loginexists($login) || $users->emailexists($email)) return $this->html->h2->invalidregdata;
    $password = md5uniq();
    $groups = tusergroups::instance();
    
    $id = $users->add($groups->defaultgroup, $login,$password, $name, $email, $url);
    if (!$id) return $this->html->h2->invalidregdata;
    
    $args = targs::instance();
    $args->add($users->getitem($id));
    $args->id = $id;
    $args->password = $password;
    $args->adminurl = litepublisher::$site->url . '/admin/users/' . litepublisher::$site->q . 'id';
    $mailtemplate = tmailtemplate::instance($this->section);
    $subject = $mailtemplate->subject($args);
    $body = $mailtemplate->body($args);
    $adminbody = $mailtemplate->adminbody($args);
    tmailer::sendtoadmin($subject, $adminbody);
    tmailer::sendmail(litepublisher::$options->name, litepublisher::$options->fromemail,
    $name, $email, $subject, $body);
    $this->registered = true;
    return $this->html->h2->successreg;
  }
  
}//class

?>