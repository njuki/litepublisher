<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tajaxcommentformplugin extends tplugin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function request($arg) {
    $this->cache = false;
    if (!empty($_GET['getuser'])) {
      $cookie = basemd5($_GET['getuser']  . litepublisher::$secret);
      $idpost = (int) $_GET['idpost'];
      $comusers = tcomusers::i($idpost);
      if ($user = $comusers->fromcookie($cookie)) {
        $data = array(
        'name' => $user['name'],
        'email' => $user['email'],
        'url' => $user['url']
        );
        
        $subscribers = tsubscribers::i();
        $data['subscribe'] = $subscribers->exists($idpost, $user['id']);
        
        return turlmap::htmlheader(false) . json_encode($data);
      } else
      return 403;
    }
    
    $commentform = tcommentform::i();
    $commentform->htmlhelper = $this;
    return turlmap::htmlheader(false) .$commentform->request(null);
  }
  
  //htmlhelper
  public function confirm($confirmid) {
    $result = tlocal::i()->ini['commentform'];
    $result['title'] = tlocal::get('default', 'confirm');
    $result['confirmid'] = $confirmid;
    $result['code'] = 'confirm';
    return json_encode($result);
  }
  
  public function geterrorcontent($s) {
    $result = array(
    'msg' => $s,
    'code' => 'error'
    );
    return json_encode($result);
  }
  
  public function sendcookies($cookies, $url) {
    $result = $cookies;
    $result['posturl'] = $url;
    $result['code'] = 'success';
    return json_encode($result);
  }
  
}//class