<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tyandexregservice extends tregservice {

    public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->data['name'] = 'yandex';
$this->data['title'] = 'Yandex';
$this->data['icon'] = 'yandex.png';
$this->data['url'] = '/yandex-oauth2callback.php';
}

public function getauthurl() {
$url = 'https://oauth.yandex.ru/authorize?response_type=code'.
$url.= '&client_id=' . $this->client_id;
$url .= '&state=' . $this->newstate();
return $url;
}

//handle callback
  public function request($arg) {
if ($err = parent::request($arg)) return $err;
$code = $_REQUEST['code'];
$resp = self::http_post('https://oauth.yandex.ru/token', array(
'code' => $code,
'client_id' => $this->client_id,
'client_secret' => $this->client_secret,
'grant_type' => 'authorization_code'
));

if ($resp) {
$tokens  = json_decode($resp);
if ($r = http::get('https://api-yaru.yandex.ru/me/?format=json&oauth_token=' . $tokens->access_token)) {
$info = json_decode($r);
return $this->adduser(array(
'service' => $this->name,
'idservice' => $info->id,
'email' => isset($info->email) ? $info->email : '',
'name' => $info->name, 
'website' => isset($info->links) && isset($info->links->www) ? $info->links->www : ''
));
}
}

return $this->errorauth();
}

public function gettab($html, $args, $lang) {
$result = $html->p($lang->yandex . litepublisher::$site->url . $this->url);
$result .= $html->getinput('text', "client_id_$this->name", tadminhtml::specchars($this->client_id), $lang->yandex_id) ;
$result .= $html->getinput('text', "client_secret_$this->name", tadminhtml::specchars($this->client_secret), $lang->yandex_secret) ;
return $result;
}

}//class