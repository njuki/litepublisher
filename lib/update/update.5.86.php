<?php
function _encrypt($s, $key) {
    $block = mcrypt_get_block_size(MCRYPT_Blowfish, MCRYPT_MODE_ECB);
    $pad = $block - (strlen($s) % $block);
    $s .= str_repeat(chr($pad), $pad);
    return mcrypt_encrypt(MCRYPT_Blowfish, $key, $s, MCRYPT_MODE_ECB);
  }

function update586() {
if (isset(litepublisher::$options->solt)) return;

  litepublisher::$options->solt = md5uniq();
litepublisher::$options->emptyhash = basemd5(litepublisher::$secret . litepublisher::$options->solt);
litepublisher::$options->securecookie = false;
litepublisher::$options->authenabled = true;

if (function_exists('mcrypt_encrypt')) {
litepublisher::$options->data['dbconfig']['password'] = _encrypt(str_rot13(base64_decode(litepublisher::$options->data['dbconfig']['password'])),
 litepublisher::$secret . litepublisher::$options->solt);
}
  
    $expired = time() + 31536000;
    $cookie = md5uniq();
    //litepublisher::$options->setcookies($cookie, $expired);
    $subdir = litepublisher::$site->subdir . '/';
    setcookie('litepubl_user_id', litepublisher::$options->user, $expired,  $subdir, false);
    setcookie('litepubl_user', $cookie, $expired, $subdir , false);
    setcookie('litepubl_user_flag', 'true', $expired, $subdir, false);
    
$cookie = basemd5((string) $cookie . litepublisher::$secret . litepublisher::$options->solt);
    litepublisher::$options->data['cookiehash'] = $cookie;
    litepublisher::$options->data['password'] = '';
      litepublisher::$options->cookieexpired = $expired;
unset(litepublisher::$options->data['cookie'], litepublisher::$options->data['authcookie']);

unset(litepublisher::$classes->items['tauthdigest']);
litepublisher::$classes->save();
}