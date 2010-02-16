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
    if (strtolower(trim($_POST['email'])) != strtolower(trim(litepublisher::$options->email))) return $this->html->h2->error;
    $password = md5uniq();
    litepublisher::$options->setpassword($password);
    $args = targs::instance();
    $args->password = $password;
    $mailtemplate = tmailtemplate::instance($this->section);
    $subject = $mailtemplate->subject($args);
    $body = $mailtemplate->body($args);
    tmailer::sendtoadmin($subject, $body);
    return $this->html->h2->success;
  }
  
}//class

?>