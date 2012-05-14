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
$aouth->urllist['callback'] = litepublisher::$site->url . $this->url;
$aouth->key = $this->client_id;
$oauth->secret = $this->client_secret;
return $oauth;
}
  
  //handle callback
  public function request($arg) {
    $this->cache = false;
      if (empty($_GET['oauth_token'])) return 403;
$oauth = $this->getoauth();
$tokens  = $oauth->getaccesstoken();
        if ($tokens  ) {
$keys = array(
'oauth_token' => $tokens['oauth_token']
);
		if ($r = http::get($oauth->geturl($keys, 'https://api.twitter.com/1/account/verify_credentials.json'))) {
        $info = json_decode($r);
        return $this->adduser(array(
'id' => $info->id,
        'name' => $info->name,
        'website' => 'http://twitter.com/account/redirect_by_id?id='.$info->id_str
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