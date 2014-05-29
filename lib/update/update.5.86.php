<?php
function update586() {
  litepublisher::$options->solt = md5uniq();

    $expired = time() + 31536000;
    $cookie = md5uniq();
    //litepublisher::$options->setcookies($cookie, $expired);
    $subdir = litepublisher::$site->subdir . '/';
    setcookie('litepubl_user_id', litepublisher::$options->user, $expired,  $subdir, false);
    setcookie('litepubl_user', $cookie, $expired, $subdir , false);
    setcookie('litepubl_user_flag', 'true', $expired, $subdir, false);
    
$cookie = basemd5((string) $cookie . litepublisher::$secret . litepublisher::$options->solt);
    litepublisher::$options->data['cookie'] = $cookie;
    litepublisher::$options->data['password'] = '';
      litepublisher::$options->cookieexpired = $expired;

unset(litepublisher::$classes->items['tauthdigest']);
litepublisher::$classes->save();
}