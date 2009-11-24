<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class tmailer {
  
  protected static function  send($from, $to, $subj, $body) {
    global $options;
    $subj =  '=?utf-8?B?'.@base64_encode($subj). '?=';
    $date = gmdate ("M d Y H:i:s", time());
    if (defined('debug'))
return tfiler::log("To: $to\nSubject: $subj\nFrom: $from\nReply-To: $from\nContent-Type: text/plain; charset=\"utf-8\"\nContent-Transfer-Encoding: 8bit\nDate: $date\nX-Priority: 3\nX-Mailer: LitePublisher mailer\n\n$body",
'mail.log');
    
    mail($to, $subj, $body,
    "From: $from\nReply-To: $from\nContent-Type: text/plain; charset=\"utf-8\"\nContent-Transfer-Encoding: 8bit\nDate: $date\nX-Priority: 3\nX-Mailer: Lite Publisher ver $options->version");
  }
  
  public static function  sendmail($fromname, $fromemail, $toname, $toemail, $subj, $body) {
    global $options;
    if ($options->mailer == 'smtp') {
      $mailer = TSMTPMailer ::Instance();
      return $mailer->mail($fromname, $toname, $toemail, $subj, $body);
    }
    
    return self::send(self::CreateEmail($fromname, $fromemail), self::CreateEmail($toname, $toemail), $subj, $body);
  }
  
  public static function CreateEmail($name, $email) {
    if (empty($name)) return $email;
    return   '=?utf-8?B?'.@base64_encode($name). '?=' . " <$email>";
  }
  
  public static function sendtoadmin($subject, $body) {
    global $options;
    self::sendmail($options->name, $options->fromemail,
    'admin', $options->email, $subject, $body);
  }
  
  public static function  SendAttachmentToAdmin($subj, $body, $filename, $attachment) {
    global $options;
    $subj =  '=?utf-8?B?'.@base64_encode($subj). '?=';
    $date = gmdate ("M d Y H:i:s", time());
    $from = self::CreateEmail($options->name, $options->fromemail);
    $to = self::CreateEmail('admin', $options->email);
    $boundary = md5(microtime());
    $textpart = "--$boundary\nContent-Type: text/plain; charset=\"UTF-8\"\nContent-Transfer-Encoding: base64\n\n";
    $textpart .= base64_encode($body);
    
    $attachpart = "--$boundary\nContent-Type: application/octet-stream; name=\"$filename\"\nContent-Disposition: attachment; filename=\"$filename\"\nContent-Transfer-Encoding: base64\n\n";
    $attachpart .= base64_encode($attachment);
    
    $body = $textpart . "\n". $attachpart;
    
    if (defined('debug'))
return tfiler::log("To: $to\nSubject: $subj\nFrom: $from\nReply-To: $from\nMIME-Version: 1.0\nContent-Type: multipart/mixed; boundary=\"$boundary\"\nDate: $date\nX-Priority: 3\nX-Mailer: Lite Publisher ver $options->version\n\n". $body,
'mail.log');
    
    mail($to, $subj, $body,
    "From: $from\nReply-To: $from\nMIME-Version: 1.0\nContent-Type: multipart/mixed; boundary=\"$boundary\"\nDate: $date\nX-Priority: 3\nX-Mailer: Lite Publisher ver $options->version");
  }
  
} //class

class TSMTPMailer extends tevents {
  //public $host;
  //public $login;
  //public $password;
  //public $port;
  
  public static function &Instance() {
    return GetInstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'smtpmailer';
    $this->data = $this->data +  array(
    'host' => '',
    'login' => '',
    'password' => '',
    'port' => 25
    );
  }
  
  public function Mail($fromname,  $toname, $toemail, $subj, $body) {
    global $options, $paths;
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
          
          $smtp->data("From: $from\nReply-To: $from\nContent-Type: text/plain; charset=\"utf-8\"\nContent-Transfer-Encoding: 8bit\nDate: $date\nSubject: $subj\nX-Priority: 3\nX-Mailer: Lite Publisher ver $options->version\n\n$body");
        }
        $smtp->Quit();
        $smtp->Close();
      }
    }
  }
  
}//class
?>