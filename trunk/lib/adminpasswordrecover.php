<?php

class TPasswordRecover extends TAdminPage {
 
 public static function &Instance() {
  return GetInstance(__class__);
 }
 
 protected function CreateData() {
  parent::CreateData();
  $this->basename = 'passwordrecover';
 }

public function Auth() {
}
 
 public function Getcontent() {
  global $Options;
  $html = &THtmlResource::Instance();
  $html->section = $this->basename;
  $lang = &TLocal::Instance();
  eval('$result = "'.  $html->form . '\n";');
  $result = str_replace("'", '"', $result);
  return $result;
 }
 
 public function ProcessForm() {
  global $Options;
  $html = &THtmlResource::Instance();
  $html->section = $this->basename;
  $lang = &TLocal::Instance();
  if (strtolower(trim($_POST['email'])) == strtolower(trim($Options->email))) {
   $password = md5(secret. uniqid( microtime()));
   $Options->SetPassword($password);
   eval('$subject = "'. $html->subject . '";');
   eval('$body = "'. $html->body . '";');
   TMailer::SentToAdmin(subject, $body);
   eval('$result = "'. $html->success . '\n";');
   return $result;
  } else {
   eval('$result = "'. $html->error . '\n";');
   return $result;
  }
 }
 
}//class

?>