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
    $html = $this->html;
    if ($this->registered) return $html->waitconfirm();
    if ($this->logged) return $html->logged();
    
    $args = targs::i();
    $form = '';
    foreach (array('email', 'name') as $name) {
      $args->$name = isset($_POST[$name]) ? $_POST[$name] : '';
      $form .= "[text=$name]";
    }
    $lang = tlocal::i('users');
    $args->formtitle = $lang->regform;
    $args->data['$lang.email'] = 'email';
    $result = $this->widget;
    if (isset($_GET['backurl'])) $result = str_replace(array('&backurl=', '&amp;backurl='), 
'&amp;backurl=' . urlencode($_GET['backurl']), $result);
    $result .= $html->adminform($form, $args);
    $this->callevent('oncontent', array(&$result));
    return $result;
  }
  
  public function processform() {
    extract($_POST, EXTR_SKIP);
    if (!tcontentfilter::ValidateEmail($email)) return '<p><strong>' .  tlocal::get('comment', 'invalidemail') . "</strong></p>\n";
    $users = tusers::i();
    if ($users->emailexists($email)) return $this->html->h2->invalidregdata;
    $password = md5uniq();
    $groups = tusergroups::i();
    
    $id = $users->add(array(
    'idgroups' => array($groups->defaultgroup),
    'password' => $password,
    'name' => $name,
    'email' => $email
    ));
    if (!$id) return $this->html->h4->invalidregdata;
    
    $args = targs::i();
    $args->add($users->getitem($id));
    $pages = tuserpages::i();
    $args->add($pages->getitem($id));
    $args->id = $id;
    $args->password = $password;
    $args->adminurl = litepublisher::$site->url . '/admin/users/' . litepublisher::$site->q . 'id';
    $mailtemplate = tmailtemplate::i($this->section);
    $subject = $mailtemplate->subject($args);
    $body = $mailtemplate->body($args);
    $adminbody = $mailtemplate->adminbody($args);
    tmailer::sendtoadmin($subject, $adminbody);
    tmailer::sendmail(litepublisher::$site->name, litepublisher::$options->fromemail,
    $name, $email, $subject, $body);
    $this->registered = true;
    return $this->html->h4->successreg;
  }
  
}//class