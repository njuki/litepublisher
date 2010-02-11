<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tpasswordrecover extends tadminmenu {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'passwordrecover';
  }
  
public function Auth() { }
public function getmenu() { return ''; }
  
  public function getcontent() {
    $html = THtmlResource::instance();
    $html->section = $this->basename;
    $lang = tlocal::instance();
    eval('$result = "'.  $html->form . '\n";');
    $result = str_replace("'", '"', $result);
    return $result;
  }
  
  public function ProcessForm() {
    $html = THtmlResource::instance();
    $html->section = $this->basename;
    $lang = tlocal::instance();
    if (strtolower(trim($_POST['email'])) == strtolower(trim(litepublisher::$options->email))) {
      $password = md5uniq();
      litepublisher::$options->setpassword($password);
      eval('$subject = "'. $html->subject . '";');
      eval('$body = "'. $html->body . '";');
      tmiler::sendtoadmin(subject, $body);
      eval('$result = "'. $html->success . '\n";');
      return $result;
    } else {
      eval('$result = "'. $html->error . '\n";');
      return $result;
    }
  }
  
}//class

?>