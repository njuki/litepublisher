<?php

class TAuthDigest extends TEventClass {
 public $stale;
 
 public static function &Instance() {
  return GetInstance(__class__);
 }
 
 protected function CreateData() {
  parent::CreateData();
  $this->basename = 'authdigest';
  $this->Data['nonce'] = '';
  $this->Data['time'] = 0;
  $this->Data['cookie'] = '';
  $this->Data['cookieenabled'] = false;
  $this->Data['cookieexpired'] = 0;
  $this->Data['xxxcheck'] = true;
  $this->stale = false;
 }
 
 public function AfterLoad() {
  parent::AfterLoad();
  if ($this->time + 600 < time()) $this->NewNonce();
 }
 
 private function NewNonce() {
  $this->Data['nonce'] = md5(secret. uniqid( microtime(), true));
  $this->Data['time'] = time();
  $this->Save();
 }
 
 private function GetDigestHeader() {
  if ($uri = preg_replace_callback('/,uri="(.*?)"/',
  create_function('$matches', 'return \',uri="\' . urlencode($matches[1]) . \'"\';'),
  $_SERVER["QUERY_STRING"])) {
   parse_str($uri, $_GET);
   parse_str($uri, $_REQUEST);
  }
  
  if (function_exists('apache_request_headers') && ini_get('safe_mode') == false) {
   $arh = apache_request_headers();
   return  isset($arh['Authorization']) ? $arh['Authorization'] : null;
  } elseif (isset($_SERVER['PHP_AUTH_DIGEST'])) {
   return $_SERVER['PHP_AUTH_DIGEST'];
  } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
   return $_SERVER['HTTP_AUTHORIZATION'];
  } elseif (isset($_ENV['PHP_AUTH_DIGEST'])) {
   return $_ENV['PHP_AUTH_DIGEST'];
  } elseif (isset($_SERVER['Authorization'])) {
   return $_SERVER['Authorization'];
  } elseif (isset($_REQUEST['HTTP_AUTHORIZATION'])) {
   return stripslashes(urldecode($_REQUEST['HTTP_AUTHORIZATION']));
  }
  return null;
 }
 
 public function Auth() {
  global $Options;
  if ($this->nonce == '') $this->NewNonce();
  if ($digest  = $this->GetDigestHeader()) {
   $digest  = substr($digest,0,7) == 'Digest ' ?  substr($digest, strpos($digest, ' ') + 1) : $digest ;
   preg_match_all('/(\w+)=(?:"([^"]+)"|([^\s,]+))/', $digest, $mtx, PREG_SET_ORDER);
   $hdr = array();
   foreach ($mtx as $m) $hdr[$m[1]] = $m[2] ? $m[2] : $m[3];
   if (count($hdr) == 0) return false;
   if ($this->nonce != $hdr['nonce'])  {
    $this->stale  = true;
    return false;
   }
   if ($Options->login != $hdr['username']) return false;
   $a1 = strtolower($Options->password);
   $a2 = md5($_SERVER['REQUEST_METHOD'] .':' . $hdr['uri']);
   return $hdr['response'] == md5("$a1:$this->nonce:$a2");
  }
  return false;
 }
 
 public function Headers() {
  global $Options;
  $protocol = $_SERVER["SERVER_PROTOCOL"];
  if ( ('HTTP/1.1' != $protocol) && ('HTTP/1.0' != $protocol) ) $protocol = 'HTTP/1.0';
  $stale = $this->stale ? 'true' : 'false';
  
  $result = "<?php
  @header('WWW-Authenticate: Digest realm=\"$Options->realm\", nonce=\"$this->nonce\", stale=\"$stale\"');
  @header('$protocol 401 Unauthorized', true, 401);
  echo '401 Unauthorized';
  ?>";
  return $result;
 }
 
}//class

?>