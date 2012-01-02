<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminpassword extends tadminform {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->section = 'password';
  }
  
  public function getcontent() {
    $args = new targs();
    $lang = tlocal::admin('password');
    $args->formtitle = $lang->enteremail;
    return $this->html->adminform('[text=login] [text=email]', $args);
  }
  
  public function processform() {
    $id = false;
    $login = trim($_POST['login']);
    $email = strtolower(trim($_POST['email']));
    if (empty($email) || empty($login)) return $this->html->h2->error;
    if (($email == strtolower(trim(litepublisher::$options->email))) && ($login == litepublisher::$options->login)) {
      $id = 1;
    } elseif (litepublisher::$options->usersenabled) {
      $users = tusers::i();
      if ($id = $users->emailexists($email)) {
        if ($login != $users->getvalue($id, 'login')) $id = false;
      }
    }
    
    if (!$id) return $this->html->h2->error;
    $password = md5uniq();
    if ($id == 1) {
      litepublisher::$options->changepassword($password);
    } else {
      $users->changepassword($id, $password);
    }
    
    $args = targs::i();
    if ($id == 1) {
      $name = 'admin';
    } else {
      $item = $users->getitem($id);
      $args->add($item);
      $name = $item['login'];
    }
    $args->login = $name;
    $args->password = $password;
    $mailtemplate = tmailtemplate::i($this->section);
    $subject = $mailtemplate->subject($args);
    $body = $mailtemplate->body($args);
    
    tmailer::sendmail(litepublisher::$site->name, litepublisher::$options->fromemail,
    $name, $email, $subject, $body);
    return $this->html->h2->success;
  }
  
}//class

?>