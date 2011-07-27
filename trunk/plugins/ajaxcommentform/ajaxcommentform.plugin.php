<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tajaxcommentformplugin extends tplugin {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function install() {
    litepublisher::$options->autocmtform = false;
    litepublisher::$urlmap->addget('/ajaxcommentform.htm', get_class($this));
    
    $jsmerger = tjsmerger::instance();
    $jsmerger->lock();
    $jsmerger->add('comments', '/plugins/' . basename(dirname(__file__)) . '/ajaxcommentform.min.js');
    $jsmerger->addtext('comments', 'ajaxform', $this->getjs());
    $jsmerger->unlock();
  }
  
  public function uninstall() {
    litepublisher::$options->autocmtform = true;
    turlmap::unsub($this);
    
    $jsmerger = tjsmerger::instance();
    $jsmerger->lock();
    $jsmerger->deletefile('comments', '/plugins/' . basename(dirname(__file__)) . '/ajaxcommentform.min.js');
    $jsmerger->deletetext('comments', 'ajaxform');
    $jsmerger->unlock();
  }
  
  public function getjs() {
    $name = basename(dirname(__file__));
    $lang = tlocal::instance('comments');
    $ls = array(
    'error_title' => $lang->error
    );
    return sprintf('ltoptions.commentform = %s;', json_encode($ls));
  }
  
  public function request($arg) {
    $this->cache = false;
    if (!empty($_GET['getuser'])) {
      $cookie = basemd5($_GET['getuser']  . litepublisher::$secret);
      $idpost = (int) $_GET['idpost'];
      $comusers = tcomusers::instance($idpost);
      if ($user = $comusers->fromcookie($cookie)) {
        $data = array(
        'name' => $user['name'],
        'email' => $user['email'],
        'url' => $user['url']
        );
        
        $subscribers = tsubscribers::instance();
        $data['subscribe'] = $subscribers->subscribed($idpost, $user['id']);
        
        return turlmap::htmlheader(false) . json_encode($data);
      } else
      return 403;
    }
    
    $commentform = tcommentform::instance();
    $commentform->htmlhelper = $this;
    return turlmap::htmlheader(false) .$commentform->request(null);
  }
  
  //htmlhelper
  public function confirm($confirmid) {
    $result = tlocal::$data['commentform'];
    $result['title'] = tlocal::$data['default']['confirm'];
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