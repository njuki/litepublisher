<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
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
    $html = $this->html;
    $args = new targs();
    $lang = tlocal::admin('password');
    if (empty($_GET['confirm'])) {
      $args->formtitle = $lang->enteremail;
      return $html->adminform('[text=email]', $args);
    } else {
      $email = $_GET['email'];
      $confirm = $_GET['confirm'];
      tsession::start('password-restore-' .md5($email));
      if (!isset($_SESSION['email']) || ($email != $_SESSION['email']) || ($confirm != $_SESSION['confirm'])) {
        if (!isset($_SESSION['email'])) session_destroy();
        return $html->h4->notfound;
      }
      session_destroy();
      if ($id = $this->getiduser($email)) {
        $password = md5uniq();
        if ($id == 1) {
          litepublisher::$options->changepassword($password);
        } else {
          tusers::i()->changepassword($id, $password);
        }
        $args->password = $password;
        $args->email = $email;
        return $html->newpassword($args);
      } else {
        return $html->h4->notfound;
      }
    }
  }
  
  public function getiduser($email) {
    if (empty($email)) returnfalse;
    if (($email == strtolower(trim(litepublisher::$options->email)))) return 1;
    return tusers::i()->emailexists($email);
  }
  
  public function processform() {
    $html = $this->html;
    $email = strtolower(trim($_POST['email']));
    if (empty($email)) return $html->h4->error;
    $id = $this->getiduser($email);
    if (!$id) return $html->h4->error;
    $args = targs::i();
    
    tsession::start('password-restore-' .md5($email));
    if (!isset($_SESSION['count'])) {
      $_SESSION['count'] =1;
    } else {
      if ($_SESSION['count']++ > 3) return $this->html->h4->outofcount;
    }
    
    $_SESSION['email'] = $email;
    $_SESSION['confirm'] = md5(mt_rand() . litepublisher::$secret. microtime());
    $args->confirm = $_SESSION['confirm'];
    session_write_close();
    
    $args->email = urlencode($email);
    if ($id == 1) {
      $name = litepublisher::$site->author;
    } else {
      $item = tusers::i()->getitem($id);
      $args->add($item);
      $name = $item['name'];
    }
    
    $mailtemplate = tmailtemplate::i($this->section);
    $subject = $mailtemplate->subject($args);
    $body = $mailtemplate->body($args);
    
    tmailer::sendmail(litepublisher::$site->name, litepublisher::$options->fromemail,
    $name, $email, $subject, $body);
    return $html->h2->success;
  }
  
}//class