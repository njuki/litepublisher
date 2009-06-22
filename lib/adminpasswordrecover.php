<?php

class TPasswordRecover extends TAdminPage {
 
 public static function &Instance() {
  return GetInstance(__class__);
 }
 
 protected function CreateData() {
  parent::CreateData();
  $this->basename = 'passwordrecover';
 }
 
 public function Request($param) {
  TLocal::LoadLangFile('admin');
  $this->title = TLocal::$data['passwordrecover']['title'];
  if (isset($_POST) && (count($_POST) > 0)) {
   if (get_magic_quotes_gpc()) {
    foreach ($_POST as $name => $value) {
     $_POST[$name] = stripslashes($_POST[$name]);
    }
   }
   $this->formresult= $this->ProcessForm();
  }
  
 }
 
 public function Getcontent() {
  global $Options;
  $html = &THtmlResource::Instance();
  $html->section = 'passwordrecover';
$lang = &TLocal::Instance();
  eval('$result = "'.  $html->form . '\n";');
  $result = str_replace("'", '"', $result);
  return $result;
 }
 
 public function ProcessForm() {
  global $Options;
  $html = &THtmlResource::Instance();
  $html->section = 'passwordrecover';
$lang = &TLocal::Instance();
  if (strtolower(trim($_POST['email'])) == strtolower(trim($Options->email))) {
   $password = md5(secret. uniqid( microtime()));
   $Options->SetPassword($password);
   eval('$subject = "'. $html->subject . '";');
   eval('$body = "'. $html->body . '";');
   TMailer::SentToAdmin(subject, $body);
   return $html->success;
  } else {
   return $html->error;
  }
 }
 
}//class

?>