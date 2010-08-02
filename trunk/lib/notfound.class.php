<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tnotfound404 extends tevents {
  
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
  
  public function  httpheader() {
    return "<?php
    @Header( 'HTTP/1.0 404 Not Found');
    @Header( 'Content-Type: text/html; charset=utf-8' );
    @Header( 'Cache-Control: no-cache, must-revalidate');
    @Header( 'Pragma: no-cache');
    ?>";
  }
  
  function getcont() {
    if ($this->notify) $this->sendmail();
    $this->cache = false;
    $result = $this->text != '' ? $this->text :  '<h2 class="center">'. tlocal::$data['default']['notfound'] . '</h2>';
    $theme = ttheme::instance();
    return sprintf($theme->content->simple, $result);
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