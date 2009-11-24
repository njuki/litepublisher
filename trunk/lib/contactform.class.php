<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class tcontactform extends tmenu {

public static function instance() {
return getinstance(__class__);
}
  
  public function processform($id) {
    global $options;
    if (!isset($_POST['contactvalue'])) return  '';
    $lang = tlocal::instance('contactform');
    $error = "<p><strong>$lang->error</strong></p>\n";

   $time = substr($_POST['contactvalue'], strlen('_contactform'));
    if (time() >  $time) return $error;
    $email = trim($_POST['email']);
    if (!tcontentfilter::ValidateEmail($email)) return '<p><strong>' .  tlocal::$data['comment']['invalidemail'] . "</strong></p>\n";
    
    $content = trim($_POST['content']);
    if (strlen($content) <= 15) return '<p><strong>' .  tlocal::$data['comment']['emptycontent'] . "</strong></p>\n";
    
    tmailer::sendmail('', $email, '', $options->email, $lang->subject, $content);

    return "<p><strong>$lang->success</strong></p>\n";
  }
  
}//class

?>