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

$r = http_post('https://accounts.google.com/o/oauth2/token', array(
'code' => $code,
'client_id' => '49489695024.apps.googleusercontent.com',
'client_secret' => 'O83YVfgJDoU2kJ7YhuuVNnK8',
'redirect_uri' => 'http://litepublisher.ru/oauth2callback.php',
'grant_type' => 'authorization_code'
));

dumpvar($r);
if ($r) {
$a = json_decode($r);
dumpvar($a);
//$access_token = $a['access_token'];
if ($r = http::get('https://www.googleapis.com/oauth2/v1/userinfo?access_token=' . $a->access_token)) {
dumpstr($r);
}
}

}
?>
<h4><a href="https://accounts.google.com/o/oauth2/auth?scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fuserinfo.email+https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fuserinfo.profile&state=%2Fprofile&redirect_uri=http%3A%2F%2Flitepublisher.ru%2Foauth2callback.php&response_type=code&client_id=49489695024.apps.googleusercontent.com">google auth</a></h4>

