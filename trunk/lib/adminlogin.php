<?php

class TAdminLogin extends TAdminPage {
private $loged;
 
 public static function &Instance() {
  return GetInstance(__class__);
 }
 
 protected function CreateData() {
  parent::CreateData();
  $this->basename = 'login';
 }

public function Auth() {
}
 
 public function Getcontent() {
  global $Options;
if ($this->loged) return '';
  $html = &THtmlResource::Instance();
  $html->section = $this->basename;
  $lang = &TLocal::Instance();
  eval('$result = "'.  $html->form . '\n";');
  $result = str_replace("'", '"', $result);
  return $result;
 }
 
 public function ProcessForm() {
  global $Options;
if ($Options->CheckLogin($_POST['login'], $_POST['password'])) {
  $auth = &TAuthDigest::Instance();
$auth->cookie = md5(secret. uniqid( microtime()));
$auth->Save();
$this->loged = true;
$expired = isset($_POST['remember']) ? time() + 1210000 : 0;
		$secure = false; //true for sssl
  return "<?php
  @setcookie('admin', '$auth->cookie', $expired,  '$Options->subdir/pda/admin', false, $secure, true);
  @header('Location: $Options->url/admin/');
  ?>";
} else {
  $html = &THtmlResource::Instance();
  $html->section = $this->basename;
  $lang = &TLocal::Instance();

eval('$result = "'. $html->error . '\n";');
return $result;
}
}
 
}//class

?>