<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class ttwitterregservice extends tregservice {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->data['name'] = 'twitter';
    $this->data['title'] = 'Twitter';
    $this->data['icon'] = 'twitter.png';
    $this->data['url'] = '/twitter-oauth1callback.php';
  }
  
  public function getauthurl() {
return $this->oauth->getrequesttoken();
  }

public function getoauth() {
$oauth = new toauth();
$oauth->set(

'callback' => litepublisher::$site->url . $this->url
);
return $oauth;
}
  
  //handle callback
  public function request($arg) {
    if ($err = parent::request($arg)) return $err;
    $code = $_REQUEST['code'];
    $resp = self::http_post('https://accounts.google.com/o/oauth2/token', array(
    'code' => $code,
    'client_id' => $this->client_id,
    'client_secret' => $this->client_secret,
    'redirect_uri' => litepublisher::$site->url . $this->url,
    'grant_type' => 'authorization_code'
    ));
    
    if ($resp) {
      $tokens  = json_decode($resp);
      if ($r = http::get('https://www.googleapis.com/oauth2/v1/userinfo?access_token=' . $tokens->access_token)) {
        $info = json_decode($r);
        return $this->adduser(array(
        'email' => isset($info->email) ? $info->email : '',
        'name' => $info->name,
        'website' => isset($info->link) ? $info->link : ''
        ));
      }
    }
    
    return $this->errorauth();
  }
  
  protected function getadmininfo($lang) {
    return array(
    'regurl' => 'https://dev.twitter.com/apps/new',
    'client_id' => 'Consumer key',
    'client_secret' =>'Consumer secret'
    );
  }
  
}//class