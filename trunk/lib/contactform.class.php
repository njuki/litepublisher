<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tcontactform extends tmenu {
  
  public static function i($id = 0) {
    return self::iteminstance(__class__, $id);
  }
  
  public function __construct() {
    parent::__construct();
    if ($id = $this->getowner()->class2id(get_class($this))) {
      $this->loaddata($id);
    }
  }
  
  protected function create() {
    parent::create();
    $this->cache = false;
    $this->data['extra'] = array();
    $this->data['subject'] = '';
    $this->data['errmesg'] = '';
    $this->data['success'] = '';
  }
  
  public function processform() {
    if (!isset($_POST['contactvalue'])) return  '';
    $time = substr($_POST['contactvalue'], strlen('_contactform'));
    if (time() >  $time) return $this->errmesg;
    $email = trim($_POST['email']);
    
    if (!tcontentfilter::ValidateEmail($email)) return sprintf('<p><strong>%s</strong></p>', tlocal::get('comment', 'invalidemail'));
    
    $content = trim($_POST['content']);
    if (strlen($content) <= 10) return sprintf('<p><strong>%s</strong></p>', tlocal::get('comment', 'emptycontent'));
    if (false !== strpos($content, '<a href')) return $this->errmesg;
    foreach ($this->data['extra'] as $name => $title) {
      if (isset($_POST[$name] )) {
        $content .= sprintf("\n\n%s:\n%s", $title, trim($_POST[$name]));
      }
    }
    
    tmailer::sendmail('', $email, '', litepublisher::$options->email, $this->subject, $content);
    return $this->success;
  }
  
}//class