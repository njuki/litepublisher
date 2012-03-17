<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tregservice extends tplugin {

    public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
$this->data['id'] = 0;
$this->data['title'] = 'service';
$this->data['icon'] = '';
$this->data['url'] = '';
}

  public function getbasename() {
    return 'plugins' . DIRECTORY_SEPARATOR . 'regservice.' . $this->id;
  }

public function install() {
if ($this->url) litepublisher::$urlmap->addget($this->url, get_class($this));
}

public function uninstall() {
turlmap::unsub($this);
}

public static function http_post($url, array $post) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_TIMEOUT, 2);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    
    $response = curl_exec($ch);
    $headers = curl_getinfo($ch);
    curl_close($ch);
    if ($headers['http_code'] != "200") return false;
    return $response;
}


public function start_session() {
      ini_set('session.use_cookies', 1);
      ini_set('session.use_trans_sid', 0);
      ini_set(session.use_only_cookies', 1);

if (tfilestorage::$memcache) {
ini_set('session.save_handler', 'memcache');
ini_set('session.save_path', 'tcp://127.0.0.1:11211');
} else {
      ini_set(session.save_handler', 'files');
}

      session_cache_limiter(false);
      //session_id (md5($this->token));
      session_start();
}

//handle callback
  public function request($arg) {
$this->cache = false;
if (empty($_REQUEST['code'])) return 403;
$this->start_session();
if (empty($_REQUEST['state']) || empty($_SESSION['state'])) return 403;
if ($_REQUEST['state'] != $_SESSION['state']) return 403;
      session_destroy();
}

public function newstate() {
$this->start_session();
$state = md5(mt_rand() . litepublisher::$secret. microtime());
$_SESSION['state'] = $state;
      session_write_close();
return $state;
}

public function getauthurl() {
$this->error('Call abstract method');
}

}//class