<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminreguser extends tadminform {
    public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->section = 'users';
  }

public function request($arg) {
if (!litepublisher::$options->usersenabled || !litepublisher::$options->reguser) return 404;
return parent::request($arg);
}
  
  public function getcontent() {
    return $this->html->regform();
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
    $args->password = $password;
$args->adminurl = litepublisher::$options->url . '/admin/users/' . litepublisher::$options->q . 'id';
    $mailtemplate = tmailtemplate::instance($this->section);
    $subject = $mailtemplate->subject($args);
    $body = $mailtemplate->body($args);
    $adminbody = $mailtemplate->adminbody($args);
tmailer::sendtoadmin($subject, $adminbody);
    tmailer::sendmail(litepublisher::$options->name, litepublisher::$options->fromemail,
$name, $email, $subject, $body);
    return $this->html->h2->successreg;
  }
  
}//class

?>