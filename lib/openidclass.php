<?php

class TOpenid extends TEventClass {
  public $keys;
  public $trusted;
  public $url;
  
  public static function &Instance() {
    return GetInstance(__class__);
  }
  
  protected function CreateData() {
    parent::CreateData();
    $this->CacheEnabled = false;
    $this->basename = 'openidserver';
    $this->AddDataMap('keys', array());
    $this->AddDataMap('trusted', array());
    $this->Data['confirm'] = false;
    $this->Data['usebigmath'] = false;
    $this->url = '/openid/';
  }
  
  public function Install() {
    $Urlmap = &TUrlmap::Instance();
    $Urlmap->AddGet($this->url, get_class($this), null);
  }
  
  public function AfterLoad() {
    parent::AfterLoad();
    $time = time();
    foreach ($this->keys as $handle => $item) {
      if ($item['expired'] < $time) unset($this->keys[$handle]);
    }
  }
  
  private function LoadBigMath() {
    global $paths;
    require_once($paths['libinclude'] . 'bigmath.php');
    if (!extension_loaded('bcmath')) {
      if (!@dl('bcmath.'. PHP_SHLIB_SUFFIX) && !@dl('php_bcmath.'. PHP_SHLIB_SUFFIX)) {
        if (!extension_loaded('gmp')) {
          @dl('gmp.'. PHP_SHLIB_SUFFIX) || @dl('php_gmp.'. PHP_SHLIB_SUFFIX);
        }
      }
    }
  }
  
  public function Request($arg) {
    if (defined('debug'))  {
      TFiler::log($_SERVER['REQUEST_URI']);
      TFiler::log(var_export($_REQUEST, true));
    }
    $this->LoadBigMath();
    TLocal::LoadLangFile('admin');
    ini_set('arg_separator.output', '&');
    
    if (!isset($_REQUEST['openid_mode'])) return $this->nomode();
    switch ($_REQUEST['openid_mode']) {
      case 'associate':
      return $this->associate();
      
      case 'cancel':
      return $this->cancel();
      
      case 'checkid_immediate':
      return $this->checkid_immediate();
      
      case 'checkid_setup':
      return $this->checkid_setup();
      
      case 'check_authentication':
      return $this->check_authentication();
      
      case 'error':
      return $this->DoError();
      
      case 'id_res':
      return $this->id_res();
      
      default:
      return $this->nomode();
    }
  }
  
  private function nomode() {
    global $Urlmap;
    $Urlmap->redir301('/');
    //return TTemplate::SimpleHtml(TLocal::$data['openidserver']['nomode']);
  }
  
  private function id_res() {
    $auth = &TAuthDigest::Instance();
    if (!$auth->Auth())  return $auth->Headers();
    return TTemplate::SimpleHtml(TLocal::$data['openidserver']['logged']);
  }
  
  private function cancel() {
    return TTemplate::SimpleHtml(TLocal::$data['openidserver']['canceled']);
  }
  
  private function GetMessage($key, $defkey) {
    $lang = &TLocal::$data['openidserver'];
    return empty($lang[$key]) ? $lang[$defkey] : $lang[$key];
  }
  
  private function error400($key ) {
    $result = "<?php @header('HTTP/1.1 400 Bad Request, true, 400); ?>";
    $result .= TTemplate::SimpleHtml($this->GetMessage($key, 'badrequest'));
    return $result;
  }
  
  private function error500($key ) {
    $result = "<?php\n@header('HTTP/1.1 500 Internal Server Error', true, 500);\n?>";
    $result .= TTemplate::SimpleHtml($this->GetMessage($key, 'internalerror'));
    return $result;
  }
  
  private function error_post( $key) {
    $result = "<?php @header('HTTP/1.1 400 Bad Request, true, 400); ?>";
    $result .= 'error:'. $this->GetMessage($key, 'badrequest');
    return $result;
  }
  
  private function redir($url) {
    return "<?php
    @header('HTTP/1.1 302 Found', true, 302);
    @header('Location: $url');
    ?>";
  }
  
  private function DoError() {
    if (!empty($_REQUEST['openid_error'])) {
      return TTemplate::SimpleHtml($_REQUEST['openid_error']);
    } else {
      return $this->error500();
    }
  }
  
  private function error_get ( $url, $key) {
    return $this->RedirKeys($url, array('
    mode' => 'error',
    'error' => $this->GetMessage($key, 'badrequest')
    ));
  }
  
  private function RedirKeys($url, $keys) {
    $keys = $this->append_openid($keys);
    $q = strpos($url, '?') ? '&' : '?';
    return $this->redir($url . $q . http_build_query ($keys));
  }
  
  private function append_openid ($array) {
    $r = array();
    foreach ($array as $key => $value) $r["openid.$key"] = $value;
    return $r;
  }
  
  //modes
  private function associate() {
    //другие типы не поддерживаются, поэтому и проверять не надо
    $assoc_type = 'HMAC-SHA1';
    $session_type = $this->usebigmath && isset($_REQUEST['openid_session_type']) && ($_REQUEST['openid_session_type'] == 'DH-SHA1') ? 'DH-SHA1' : '';
    
    $p = '155172898181473697471232257763715539915724801966915404479707' .
    '7953140576293785419175806512274236981889937278161526466314385615958256881888' .
    '8995127215884267541995034125870655654980358010487053768147672651325574704076' .
    '5857479291291572334510643245094715007229621094194349783925984760375594985848' .
    '253359305585439638443';;
    
    $dh_modulus = !empty($_REQUEST['openid_dh_modulus'])
    ? long(base64_decode($_REQUEST['openid_dh_modulus']))
    : ($session_type == 'DH-SHA1' ? $p : null);
    
    $dh_gen = !empty($_REQUEST['openid_dh_gen'])
    ? long(base64_decode($_REQUEST['openid_dh_gen']))
    : ($session_type == 'DH-SHA1' ? 2 : null);
    
    if (!empty($_REQUEST['openid_dh_consumer_public'])) {
      $dh_consumer_public = $_REQUEST['openid_dh_consumer_public'];
    }elseif ($session_type == 'DH-SHA1') {
      return $this->error_post('dh_consumer_public');
    } else {
      $dh_consumer_public =  null;
    }
    
    $this->NewKeys($assoc_handle,$shared_secret, $lifetime);
    
    $keys = array(
    'assoc_type' => $assoc_type,
    'expires_in' => 	$lifetime,
    'assoc_handle' => $assoc_handle
    );
    
    switch ($session_type) {
      case 'DH-SHA1':
      $keys['session_type'] = $session_type;
      // Compute the Diffie-Hellman stuff
      $private_key = random($dh_modulus);
      $public_key = bmpowmod($dh_gen, $private_key, $dh_modulus);
      $remote_key = long(base64_decode($dh_consumer_public));
      $ss = bmpowmod($remote_key, $private_key, $dh_modulus);
      $keys['dh_server_public'] = base64_encode(bin($public_key));
      $keys['enc_mac_key'] = base64_encode(x_or(sha1(bin($ss)), $shared_secret));
      break;
      
      default:
      $keys['mac_key'] = base64_encode($shared_secret);
    }
    
    return $this->GetResult($keys);
  }
  
  private function GetResult( $keys ) {
    $result = "<?php @header('Content-Type: text/plain; charset=utf-8'); ?>";
    foreach ($keys as $key => $value) $result .= "$key:$value\n";
    return $result;
  }
  
  private function NewKeys(&$assoc_handle,&$shared_secret, &$lifetime) {
    $assoc_handle = md5(mt_rand() . secret);
    $shared_secret = new_secret();
    $lifetime = time() + 1200;
    
    $this->keys[$assoc_handle] = array(
    'secret' => $shared_secret ,
    'expired' => 	$lifetime
    );
    $this->Save();
  }
  
  private function check_authentication() {
    if (empty($_REQUEST['openid_assoc_handle'])) return $this->error_post('assochandle');
    $assoc_handle = $_REQUEST['openid_assoc_handle'];
    
    if(empty($_REQUEST['openid_sig'])) return $this->error_post('sig');
    $sig = $_REQUEST['openid_sig'];
    
    if (empty($_REQUEST['openid_signed'])) return $this->error_post('signed');
    $signed = $_REQUEST['openid_signed'];
    
    // Prepare the return keys
    $keys = array('openid.mode' => 'id_res');
    
    // Invalidate the assoc handle if we need to
    if (!empty($_REQUEST['openid_invalidate_handle'])) {
      if (isset($this->keys[$_REQUEST['openid_invalidate_handle']]) ){
        unset($this->keys[$_REQUEST['openid_invalidate_handle']]);
        $this->Save();
      }
      $keys['invalidate_handle'] = $_REQUEST['openid_invalidate_handle'];
    }
    
    // Validate the sig by recreating the kv pair and signing
    $tokens = '';
    $_REQUEST['openid_mode'] = 'id_res';
    foreach (explode(',', $signed) as $param) {
      $param = str_replace('.', '_', $param);
      $tokens .= sprintf("%s:%s\n", $param, $_REQUEST["openid_$param"]);
    }
    
    if ($shared_secret= $this->GetSecret($assoc_handle)) {
      $ok = base64_encode(hmac($shared_secret, $tokens));
      $keys['is_valid'] = $sig == $ok ? 'true' : 'false';
    } else {
      $keys['is_valid'] = 'false';
    }
    
    return $this->GetResult($keys);
  }
  
  private function GetSecret($handle) {
    if (isset($this->keys[$handle])) {
      if (time() > $this->keys[$handle]['expired']) return false;
      return $this->keys[$handle]['secret'];
    }
    return false;
  }
  
  private function checkid_immediate() {
    return $this->checkid(false);
  }
  
  private function checkid_setup() {
    return $this->checkid(true);
  }
  
  private function checkid ( $wait ) {
    global $Options;
    if (empty($_REQUEST['openid_return_to'])) return $this->error400('return_to');
    $return_to = $_REQUEST['openid_return_to'];
    
    if (empty($_REQUEST['openid_identity'])) return $this->error_get($return_to, 'identity');
    $identity = $_REQUEST['openid_identity'];
    if ($identity != $Options->url. $this->url)  return $this->error_get($return_to, 'identity');
    
    $trust_root = !empty($_REQUEST['openid_trust_root']) ? $_REQUEST['openid_trust_root'] : $return_to;
    if ($trust_root != $return_to) {
      if (! $this->urldescends($return_to, $trust_root)) return $this->error500('Invalidtrust');
    }
    
    $assoc_handle = !empty($_REQUEST['openid_assoc_handle']) ? $_REQUEST['openid_assoc_handle'] : null;
    $sreg_required = !empty($_REQUEST['openid_sreg_required'] )? $_REQUEST['openid_sreg_required'] : '';
    $sreg_optional = !empty($_REQUEST['openid_sreg_optional']) ? $_REQUEST['openid_sreg_optional'] : '';
    //join  fields
    $sreg_required .= ',' . $sreg_optional;
    
    $auth = &TAuthDigest::Instance();
    if (!$auth->Auth())  return $auth->Headers();
    
    $q = strpos($return_to, '?') ? '&' : '?';
    $cancel_url = $return_to . $q . 'openid.mode=cancel';
    
    if ($wait && (!in_array($trust_root, $this->trusted) || $this->confirm)) {
      //вывести форму и проверит результат формы
      if (empty($_POST['submit'])) {
        $html = THtmlResource::Instance();
        $html->section = 'openidserver';
        $lang = TLocal::Instance();
        eval('$form = "'. $html->trustform . '\n";');
        return TTemplate::SimpleHtml($form);
      } else {
        switch ($_POST['accept']) {
          case 'yes':
          break;
          
          case 'yesall':
          $this->trusted[] = $trust_root;
          $this->Save();
          break;
          
          default:
          return $this->redir($cancel_url );
        }
      }
      
    }
    
    $keys = array(
    'mode' => 'id_res',
    'identity' => $Options->url . $this->url,
    'return_to' => $return_to
    );
    
    if (!($shared_secret= $this->GetSecret($assoc_handle))) {
      if ($assoc_handle != null) {
        $keys['invalidate_handle'] = $assoc_handle;
        if (isset($this->keys[$assoc_handle])) unset($this->keys[$assoc_handle]);
      }
      $this->NewKeys($assoc_handle,$shared_secret, $lifetime);
    }
    
    $keys['assoc_handle'] = $assoc_handle;
    
    foreach (explode(',', $sreg_required) as $key) {
      if (! isset($_REQUEST[$key])) continue;
      $skey = 'sreg.' . $key;
      if ($value = $this->GetReg($key))  $keys[$skey] = $value;
    }
    
    $tokens = '';
    foreach ($keys as $key => $value) $tokens .= "$key:$value\n";
    
    $keys['signed'] = implode(',', array_keys($keys));
    $keys['sig'] = base64_encode(hmac($shared_secret, $tokens));
    return $this->RedirKeys($return_to, $keys);
  }
  
  private function GetReg($key) {
    $profile = &TProfile::Instance();
    switch ($key) {
      case 'nickname':
      case 'fullname':
      return $profile->nick;
      
      case 'email':
      return $profile->mbox;
      case 'gender':
      return $profile->gender;
      
      case 'country':
      return $profile->country;
      
      case 'dob':
      return $profile->dateOfBirth;
      
      default:
      return false;
    }
  }
  
  private function urldescends ( $child, $parent ) {
    if ($child == $parent )return true;
    $keys = array();
    $parts = array();
    $req = array('scheme', 'host');
    $bad = array('fragment', 'pass', 'user');
    foreach (array('parent', 'child') as $name) {
      $parts[$name] = @parse_url($$name);
      if ($parts[$name] === false) return false;
      $keys[$name] = array_keys($parts[$name]);
      if (array_intersect($keys[$name], $req) != $req) return false;
      if (array_intersect($keys[$name], $bad) != array()) return false;
      if (! preg_match('/^https?$/i', strtolower($parts[$name]['scheme']))) return false;
      if (! array_key_exists('port', $parts[$name])) $parts[$name]['port'] =  (strtolower($parts[$name]['scheme']) == 'https') ? 443 : 80;
      if (! array_key_exists('path', $parts[$name])) $parts[$name]['path'] = '/';
    }
    
    // port and scheme must match
    if ($parts['parent']['scheme'] != $parts['child']['scheme'] ||
    $parts['parent']['port'] != $parts['child']['port']) return false;
    
    // compare the hosts by reversing the strings
    $cr_host = strtolower(strrev($parts['child']['host']));
    $pr_host = strtolower(strrev($parts['parent']['host']));
    $break = str_diff_at($cr_host, $pr_host);
    if ($break >= 0 && ($pr_host[$break] != '*' || substr_count(substr($pr_host, 0, $break), '.') < 2) ) return false;
    // now compare the paths
    $break = str_diff_at($parts['child']['path'], $parts['parent']['path']);
    if ($break >= 0
    && ($break < strlen($parts['parent']['path']) && $parts['parent']['path'][$break] != '*')
    || ($break > strlen($parts['child']['path'])))
    return false;
    
    return true;
  }
  
  
}//class

?>