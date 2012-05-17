<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminreguser extends tadminform {
  private $registered;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'admin.reguser';
    $this->addevents('oncontent');
    $this->data['widget'] = '';
    $this->section = 'users';
    $this->registered = false;
  }
  
  public function request($arg) {
    if (!litepublisher::$options->usersenabled || !litepublisher::$options->reguser) return 403;
    return parent::request($arg);
  }
  
  public function gettitle() {
    return tlocal::get('users', 'adduser');
  }
  
  public function getlogged() {
    if (litepublisher::$options->cookieenabled) {
      return litepublisher::$options->authcookie();
    } else {
      $auth = tauthdigest::i();
      return $auth->auth();
    }
  }
  
  public function getcontent() {
$result = '';
    $html = $this->html;
    if ($this->registered) return $html->waitconfirm();
    if ($this->logged) return $html->logged();
    
    $args = targs::i();
if (!empty($_GET['confirm'])) {
$confirm = $_GET['confirm'];
      $email = $_GET['email'];
    tsession::start('reguser-' . md5($email));
      if (!isset($_SESSION['email']) || ($email != $_SESSION['email']) || ($confirm != $_SESSION['confirm'])) {
        if (!isset($_SESSION['email']) session_destroy();
        return $html->h4->invalidregdata;
      }

$backurl = $_SESSION['backurl'];

    $users = tusers::i();    
    $id = $users->add(array(
    'password' => $_SESSION['password'],
    'name' => $_SESSION['name'],
    'email' => $_SESSION['email']
    ));

        session_destroy();
    if ($id) {
$loginurl = litepublisher::$site->url . '/admin/login/';
if (backurl) {
$loginurl .=  litepublisher::$site->q == '?' '?' : '&amp;';
$loginurl .= 'backurl=' .urlencode($backurl);
}
    return $this->html->h4->successreg;
}

$result .= $html->h4->invalidregdata;
} 

    $form = '';
    foreach (array('email', 'name') as $name) {
      $args->$name = isset($_POST[$name]) ? $_POST[$name] : '';
      $form .= "[text=$name]";
    }
    $lang = tlocal::i('users');
    $args->formtitle = $lang->regform;
    $args->data['$lang.email'] = 'email';
    $result .= $this->widget;
    if (isset($_GET['backurl'])) $result = str_replace(array('&backurl=', '&amp;backurl='),
    '&amp;backurl=' . urlencode($_GET['backurl']), $result);
    $result .= $html->adminform($form, $args);
    $this->callevent('oncontent', array(&$result));
    return $result;
  }
  
  public function processform() {
    extract($_POST, EXTR_SKIP);
    if (!tcontentfilter::ValidateEmail($email)) return sprintf('<p><strong>%s</strong></p>' tlocal::get('comment', 'invalidemail'));
    $users = tusers::i();
    if ($users->emailexists($email)) return $this->html->h2->invalidregdata;
    tsession::start('reguser-' . md5($email));
$_SESSION['email'] = $email;
$_SESSION['name'] = $name;
$confirm = md5(mt_rand() . litepublisher::$secret. microtime());
    $_SESSION['confirm'] = $confirm;
    $password = md5uniq();
$_SESSION['password'] = $password;
$_SESSION['backurl'] = isset($_GET['backurl']) ? $_GET['backurl'] : '';
      session_write_close();

    $args = new targs();
    $args->name = $name;
$args->email = urlencode($email);
$args->confirm = $confirm;
    $args->password = $password;
    $args->confirmurl = litepublisher::$site->url . '/admin/reguser/' . litepublisher::$site->q . 'email';

    $mailtemplate = tmailtemplate::i($this->section);
    $subject = $mailtemplate->subject($args);
    $body = $mailtemplate->body($args);

    tmailer::sendmail(litepublisher::$site->name, litepublisher::$options->fromemail,
    $name, $email, $subject, $body);

$this->registered = true;
}

}//class