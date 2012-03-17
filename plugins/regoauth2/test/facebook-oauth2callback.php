<?php
      Header( 'Cache-Control: no-cache, must-revalidate');
      Header( 'Pragma: no-cache');
    error_reporting(E_ALL | E_NOTICE | E_STRICT | E_WARNING );
    ini_set('display_errors', 1);

define('litepublisher_mode', 'xmlrpc');
include('index.php');
litepublisher::$debug = true;

dumpvar($_GET);
dumpvar($_POST);

function http_post($url, array $post) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    
    //curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    //curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 2);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    
    $response = curl_exec($ch);
    $headers = curl_getinfo($ch);
    curl_close($ch);
    if ($headers['http_code'] != "200") return false;
    return $response;
}

if (!empty($_GET['code'])) {
$code = $_GET['code'];

$r = http::get('https://graph.facebook.com/oauth/access_token?' . http_build_query(array(
'code' => $code,
'client_id' => '290433841025058',
'client_secret' => '662bff30c983de30e704e9ca801015a6',
'redirect_uri' => 'http://litepublisher.ru/facebook-oauth2callback.php'
)));

dumpvar($r);

if ($r) {
     $params = null;
     parse_str($r, $params);
dumpvar($params);
if ($r = http::get('https://graph.facebook.com/me?access_token=' . $params['access_token'])) {
dumpstr($r);
$j = json_decode($r);
dumpvar($j);
}
}

}
?>
<h4><a href="https://www.facebook.com/dialog/oauth?redirect_uri=http%3A%2F%2Flitepublisher.ru%2Ffacebook-oauth2callback.php&response_type=code&scope=email&client_id=290433841025058">Facebook auth</a></h4>

