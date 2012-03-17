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

function sign_server_server(array $request_params, $secret_key) {
  ksort($request_params);
  $params = '';
  foreach ($request_params as $key => $value) {
    $params .= "$key=$value";
  }
  return md5($params . $secret_key);
}
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

$r = http_post('https://connect.mail.ru/oauth/token', array(
'code' => $code,
'client_id' => '668167',
'client_secret' => '52f3e0029ea47940475ee611aee559bc',
'redirect_uri' => 'http://litepublisher.ru/mailru-oauth2callback.php',
'grant_type' => 'authorization_code'
));

dumpvar($r);
if ($r) {
$a = json_decode($r);
dumpvar($a);

$params = array(
'method' => 'users.getInfo',
'app_id' => '668167',
'session_key' => $a->access_token,
//'uids' => $a->x_mailru_vid,
'secure' => '1',
'format' => 'json',
);
ksort($params);
$params['sig'] = sign_server_server($params, '52f3e0029ea47940475ee611aee559bc');
if ($r = http::get('http://www.appsmail.ru/platform/api?' . http_build_query($params))) {
dumpstr($r);
$a = json_decode($r);
dumpvar($a);
}

}

}
?>
<h4><a href="https://connect.mail.ru/oauth/authorize?redirect_uri=http%3A%2F%2Flitepublisher.ru%2fmailru-oauth2callback.php&response_type=code&client_id=668167">Mail.ru auth</a></h4>