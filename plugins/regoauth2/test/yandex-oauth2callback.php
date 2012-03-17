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

$r = http_post('https://oauth.yandex.ru/token', array(
'code' => $code,
'client_id' => '7b88f0d5e4fe4813a86921836a166275',
'client_secret' => 'c5d7f77f228f4dd9a9f138364ae0b957',
'grant_type' => 'authorization_code'
));

dumpvar($r);
if ($r) {
$a = json_decode($r);
dumpvar($a);
exit();
//$access_token = $a['access_token'];
if ($r = http::get('https://www.googleapis.com/oauth2/v1/userinfo?access_token=' . $a->access_token)) {
dumpstr($r);
}
}

}
?>
<h4><a href="https://oauth.yandex.ru/authorize?response_type=code&client_id=7b88f0d5e4fe4813a86921836a166275">Yandex auth</a></h4>
