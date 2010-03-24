<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminpassword extends tadminform {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->section = 'password';
  }
  
  public function getcontent() {
    return $this->html->form();
  }
  
  public function processform() {
    $email = strtolower(trim($_POST['email']));
    if (litepublisher::$options->usersenabled) {
      $users = tusers::instance();
      $id = $users->emailexists($email);
    } else {
      $id = $email == strtolower(trim(litepublisher::$options->email))  ? 1 : false;
    }
    if (!$id) return $this->html->h2->error;
    $password = md5uniq();
    if ($id == 1) {
      litepublisher::$options->setpassword($password);
    } else {
      $users->setpassword($id, $password);
    }
    
    $args = targs::instance();
    if ($id == 1) {
      $name = 'admin';
    } else {
      $item = $users->getitem($id);
      $args->add($item);
      $name = $item['name'];
    }
      $args->login = $name;
    $args->password = $password;
    $mailtemplate = tmailtemplate::instance($this->section);
    $subject = $mailtemplate->subject($args);
    $body = $mailtemplate->body($args);
    
    tmailer::sendmail(litepublisher::$options->name, litepublisher::$options->fromemail,
    $name, $email, $subject, $body);
    return $this->html->h2->success;
  }
  
}//class

?>