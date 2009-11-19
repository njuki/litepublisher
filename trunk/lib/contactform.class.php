<?php

class tcontactform extends tmenuitem {
  
  protected function create() {
    parent::create();
    $this->CacheEnabled = false;
  }
  
  public function getcontent() {
    $result = '';
    if (isset($_POST) && isset($_POST['email'])) {
      $result .= $this->processform();
    }
    
    $result .= $this->Data['content'];
    return $result;
  }
  
  public function ProcessForm() {
    global $Options;
    $lang = &TLocal::$data['contactform'];
    $error = '<p><strong>'. $lang['error'] . "</strong></p>\n";
    if (!isset($_POST['FormValue']))  return  $error;
    $TimeKey = substr($_POST['FormValue'], strlen('_Value'));
    if (time() >  $TimeKey) return $error;
    $email = trim($_POST['email']);
    if (!TContentFilter::ValidateEmail($email)) {
      return '<p><strong>' .  TLocal::$data['comment']['invalidemail'] . "</strng></p>\n";
    }
    
    $content = trim($_POST['content']);
    if (strlen($content) <= 15) {
      return '<p><strong>' .  TLocal::$data['comment']['emptycontent'] . "</strong></p>\n";
    }
    
    TMailer::SendMail('', $email, '', $Options->email, $lang['subject'], $content);
    return '<p><strong>' . $lang['success'] . "</strong></p>\n";
  }
  
}//class

?>