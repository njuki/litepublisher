<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tcontactform extends tmenu {
  
  public static function instance($id = 0) {
    return self::iteminstance(__class__, $id);
  }
  
  protected function create() {
    parent::create();
    $this->cache = false;
$this->data['subject'] = '';
$this->data['errmesg'] = '';
$this->data['success'] = '';
  }
  
  public function processform() {
    if (!isset($_POST['contactvalue'])) return  '';
    $time = substr($_POST['contactvalue'], strlen('_contactform'));
    if (time() >  $time) return $this->errmesg;
    $email = trim($_POST['email']);

    if (!tcontentfilter::ValidateEmail($email)) return sprintf('<p><strong>%s</strong></p>', tlocal::$data['comment']['invalidemail']);
    
    $content = trim($_POST['content']);
    if (strlen($content) <= 15) return sprintf('<p><strong>%s</strong></p>', tlocal::$data['comment']['emptycontent']);
if (false !== strpos($content, '<a href')) return $this->errmesg;
    
    tmailer::sendmail('', $email, '', litepublisher::$options->email, $this->subject, $content);
    return $this->success;
  }
  
}//class

?>