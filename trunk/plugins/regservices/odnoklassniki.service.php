<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class todnoklassnikiservice extends tregservice {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->data['appkey'] = '';
    $this->data['name'] = 'odnoklassniki';
    $this->data['title'] = 'odnoklassniki.ru';
    $this->data['icon'] = 'odnoklassniki.png';
    $this->data['url'] = '/odnoklassniki-oauth2callback.php');
  }
  
  public function getauthurl() {
    $url = 'http://www.odnoklassniki.ru/oauth/authorize?';
    $url .= parent::getauthurl();
    return $url;
  }
  
  //handle callback
  public function sign(array $request_params, $secret_key) {
    ksort($request_params);
    $params = '';
    foreach ($request_params as $key => $value) {
      $params .= "$key=$value";
    }
    return md5($params . $secret_key);
  }
  
  public function request($arg) {
    if ($err = parent::request($arg)) return $err;
    $code = $_REQUEST['code'];
    $resp = self::http_post('http://api.odnoklassniki.ru/oauth/token.do', array(
			'grant_type' => 'authorization_code',
    'code' => $code,
    'client_id' => $this->client_id,
    'client_secret' => $this->client_secret,
    'redirect_uri' => litepublisher::$site->url . $this->url,
    ));
    
    if ($resp) {
      $tokens  = json_decode($resp);
      
      $params = array(
               'application_key' => $this->appkey,
				'client_id' => $this->client_id,
				'method' => 'users.getCurrentUser',
                'format' => 'JSON',
      );
      
      $params['sig'] = strtolower($this->sign($params, md5($tokens->access_token . $this->client_secret)));
                $params['access_token'] = $tokens->access_token;

      if ($r = self::http_post('http://api.odnoklassniki.ru/fb.do', $params)) {
        $js = json_decode($r);
if (!isset($js->error)) {
        return $this->adduser(array(
        'uid' => $js->uid,
        'email' => $js->has_email ? $js->email : '',
        'name' => $js->name,
        'website' => isset($js->link) ? $js->link : ''
        ));
      }
}
    }
    
    return $this->errorauth();
  }
  
  protected function getadmininfo($lang) {
    return array(
    'regurl' => 'http://api.mail.ru/sites/my/add',
    'client_id' => $lang->odnoklass_id,
    'client_secret' =>$lang->mailru_secret,
'appkey' => $lang->odnoklass_appkey
    );
  }
  
}//class