<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tmailer {
  private static $hold;
  
  protected static function  send($from, $to, $subj, $body) {
    $options =     litepublisher::$options;
    $subj =  '=?utf-8?B?'.@base64_encode($subj). '?=';
    $date = date('r');
    if (litepublisher::$debug) {
      $dir = litepublisher::$paths->data . 'logs' . DIRECTORY_SEPARATOR ;
      if (!is_dir($dir)) {
        mkdir($dir, 0777);
        @chmod($dir, 0777);
      }
      $eml = "To: $to\nSubject: $subj\nFrom: $from\nReply-To: $from\nContent-Type: text/plain; charset=\"utf-8\"\nContent-Transfer-Encoding: 8bit\nDate: $date\nX-Priority: 3\nX-Mailer: LitePublisher mailer\n\n$body";
      return file_put_contents($dir . date('H-i-s.d.m.Y.\e\m\l'), $eml);
    }
    
    mail($to, $subj, $body,
    "From: $from\nReply-To: $from\nContent-Type: text/plain; charset=\"utf-8\"\nContent-Transfer-Encoding: 8bit\nDate: $date\nX-Priority: 3\nX-Mailer: Lite Publisher ver " . litepublisher::$options->version);
  }
  
  public static function  sendmail($fromname, $fromemail, $toname, $toemail, $subj, $body) {
    if (litepublisher::$options->mailer == 'smtp') {
      $mailer = TSMTPMailer ::Instance();
      return $mailer->mail($fromname, $toname, $toemail, $subj, $body);
    }
    
    return self::send(self::CreateEmail($fromname, $fromemail), self::CreateEmail($toname, $toemail), $subj, $body);
  }
  
  public static function CreateEmail($name, $email) {
    if (empty($name)) return $email;
    return   '=?utf-8?B?'.@base64_encode($name). '?=' . " <$email>";
  }
  
  public static function sendtoadmin($subject, $body, $onshutdown = false) {
    if ($onshutdown) {
      if (!isset(self::$hold)) {
        self::$hold = array();
        register_shutdown_function(__class__ . '::onshutdown');
      }
      self::$hold[] = array('subject' => $subject, 'body' => $body);
      return;
    }
    
    self::sendmail(litepublisher::$site->name, litepublisher::$options->fromemail,
    'admin', litepublisher::$options->email, $subject, $body);
  }
  
  public static function onshutdown() {
    foreach (self::$hold as $i => $item) {
      self::sendtoadmin($item['subject'], $item['body'], false);
      unset(self::$hold[$i]);
    }
  }
  
  public static function  SendAttachmentToAdmin($subj, $body, $filename, $attachment) {
    $options =     litepublisher::$options;
    $subj =  '=?utf-8?B?'.@base64_encode($subj). '?=';
    $date = date('r');
    $from = self::CreateEmail(litepublisher::$site->name, $options->fromemail);
    $to = self::CreateEmail('admin', $options->email);
    $boundary = md5(microtime());
    $textpart = "--$boundary\nContent-Type: text/plain; charset=\"UTF-8\"\nContent-Transfer-Encoding: base64\n\n";
    $textpart .= base64_encode($body);
    
    $attachpart = "--$boundary\nContent-Type: application/octet-stream; name=\"$filename\"\nContent-Disposition: attachment; filename=\"$filename\"\nContent-Transfer-Encoding: base64\n\n";
    $attachpart .= base64_encode($attachment);
    
    $body = $textpart . "\n". $attachpart;
    
    if (litepublisher::$debug)
    return tfiler::log("To: $to\nSubject: $subj\nFrom: $from\nReply-To: $from\nMIME-Version: 1.0\nContent-Type: multipart/mixed; boundary=\"$boundary\"\nDate: $date\nX-Priority: 3\nX-Mailer: Lite Publisher ver $options->version\n\n". $body,
    'mail.log');
    
    mail($to, $subj, $body,
    "From: $from\nReply-To: $from\nMIME-Version: 1.0\nContent-Type: multipart/mixed; boundary=\"$boundary\"\nDate: $date\nX-Priority: 3\nX-Mailer: Lite Publisher ver " . litepublisher::$options->version);
  }
  
} //class

class TSMTPMailer extends tevents {
  //public $host;
  //public $login;
  //public $password;
  //public $port;
  
  public static function instance() {
    return getinstance(__class__);
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
    $options =     litepublisher::$options;
    include_once(litepublisher::$paths->libinclude . 'class-smtp.php');
    $smtp = new SMTP();
    if($smtp->Connect($this->host, $this->port, 10)) {
      $smtp->Hello($_SERVER['SERVER_NAME']);
      if ($smtp->Authenticate($this->login, $this->password)) {
        if ($smtp->Mail($this->login) && $smtp->Recipient($toemail)) {
          $subj =  '=?utf-8?B?'.@base64_encode($subj). '?=';
          $date = date('r');
          $from = tmailer::CreateEmail($fromname, $fromemail);
          $to = tmailer::CreateEmail($toname, $toemail);
          
          $smtp->data("From: $from\nReply-To: $from\nContent-Type: text/plain; charset=\"utf-8\"\nContent-Transfer-Encoding: 8bit\nDate: $date\nSubject: $subj\nX-Priority: 3\nX-Mailer: Lite Publisher ver $options->version\n\n$body");
        }
        $smtp->Quit();
        $smtp->Close();
      }
    }
  }
  
}//class
?>