<?php

class TMailer {
 
 protected static function  Send($from, $to, $subj, $body) {
  global $Options;
  $subj =  '=?utf-8?B?'.@base64_encode($subj). '?=';
  $date = gmdate ("M d Y H:i:s", time());
  if (defined('debug'))
  return file_put_contents($GLOBALS['paths']['home']. 'mail.eml', "To: $to\nSubject: $subj\nFrom: $from\nReply-To: $from\nContent-Type: text/plain; charset=\"utf-8\"\nContent-Transfer-Encoding: 8bit\nDate: $date\nX-Priority: 3\nX-Mailer: LitePublisher mailer\n\n$body");
  
  mail($to, $subj, $body,
  "From: $from\nReply-To: $from\nContent-Type: text/plain; charset=\"utf-8\"\nContent-Transfer-Encoding: 8bit\nDate: $date\nX-Priority: 3\nX-Mailer: Lite Publisher ver $Options->version");
 }
 
 public static function  SendMail($fromname, $fromemail, $toname, $toemail, $subj, $body) {
  global $Options;
  if ($Options->mailer == 'smtp') {
   $mailer = TSMTPMailer ::Instance();
   return $mailer->mail($fromname, $toname, $toemail, $subj, $body);
  }
  
  return self::Send(self::CreateEmail($fromname, $fromemail), self::CreateEmail($toname, $toemail), $subj, $body);
 }
 
 public static function CreateEmail($name, $email) {
  if (empty($name)) return $email;
  return   '=?utf-8?B?'.@base64_encode($name). '?=' . " <$email>";
 }
 
 public static function SentToAdmin($subject, $body) {
  global $Options;
  self::SendMail($Options->name, $Options->fromemail,
  $Options->authorname, $Options->email, $subject, $body);
 }
 
} //class

class TSMTPMailer extends TEventClass {
 //public $host;
 //public $login;
 //public $password;
 //public $port;
 
 public static function &Instance() {
  return GetInstance(__class__);
 }
 
 protected function CreateData() {
  parent::CreateData();
  $this->basename = 'smtpmailer';
  $this->Data = $this->Data +  array(
  'host' => '',
  'login' => '',
  'password' => '',
  'port' => 25
  );
 }
 
 public function Mail($fromname,  $toname, $toemail, $subj, $body) {
  global $Options, $paths;
  include_once($paths['libinclude'] . 'class-smtp.php');
  $smtp = new SMTP();
  if($smtp->Connect($this->host, $this->port, 10)) {
   $smtp->Hello($_SERVER['SERVER_NAME']);
   if ($smtp->Authenticate($this->login, $this->password)) {
    if ($smtp->Mail($this->login) && $smtp->Recipient($toemail)) {
     $subj =  '=?utf-8?B?'.@base64_encode($subj). '?=';
     $date = gmdate ("M d Y H:i:s", time());
     $from = TMailer::CreateEmail($fromname, $fromemail);
     $to = TMailer::CreateEmail($toname, $toemail);
     
     $smtp->Data("From: $from\nReply-To: $from\nContent-Type: text/plain; charset=\"utf-8\"\nContent-Transfer-Encoding: 8bit\nDate: $date\nSubject: $subj\nX-Priority: 3\nX-Mailer: Lite Publisher ver $Options->version\n\n$body");
    }
    $smtp->Quit();
    $smtp->Close();
   }
  }
 }
 
}//class
?>