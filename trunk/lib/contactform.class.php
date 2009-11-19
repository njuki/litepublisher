<?php

class tcontactform {
  
  public function processform($id) {
    global $options;
    $lang = tlocal::instance('contactform');
    $error = "<p><strong>$lang->error</strong></p>\n";
    if (!isset($_POST['FormValue'])) return  $error;

    $TimeKey = substr($_POST['FormValue'], strlen('_Value'));
    if (time() >  $TimeKey) return $error;
    $email = trim($_POST['email']);
    if (!tcontentfilter::ValidateEmail($email)) return '<p><strong>' .  tlocal::$data['comment']['invalidemail'] . "</strong></p>\n";
    
    $content = trim($_POST['content']);
    if (strlen($content) <= 15) return '<p><strong>' .  tlocal::$data['comment']['emptycontent'] . "</strong></p>\n";
    
    tmailer::SendMail('', $email, '', $options->email, $lang['subject'], $content);

    return "<p><strong>$lang->success</strong></p>\n";
  }
  
}//class

?>