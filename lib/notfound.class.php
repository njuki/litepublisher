<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tnotfound404 extends tevents implements itemplate {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->data['notify'] = true;
    $this->basename = 'notfound';
    $this->data['text'] = '';
    $this->data['tmlfile'] = '';
    $this->data['theme'] = '';
  }
  
public function request($arg) {}
public function gettitle() {}
public function getkeywords() {}
public function getdescription() {}
public function gethead() {}
  
  
  
  
  public function  httpheader() {
    return "<?php Header( 'HTTP/1.0 404 Not Found'); ?>" . turlmap::httpheader(false);
  }
  
  function getcont() {
    $this->cache = false;
    if ($this->notify) $this->sendmail();
    
    $theme = ttheme::instance();
    if ($this->text == '') {
      $lang = tlocal::instance('default');
      return $theme->parse($theme->content->notfound);
    } else {
      return $theme->simple($this->text);
    }
  }
  
  private function sendmail() {
    $args = targs::instance();
    $args->url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $args->ref =  isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    $mailtemplate = tmailtemplate::instance('notfound');
    $subject = $mailtemplate->subject($args);
    $body = $mailtemplate->body($args);
    tmailer::sendtoadmin($subject, $body, true);
  }
  
}//class

?>