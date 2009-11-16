<?php

class tnotfound404 extends TEventClass {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'notfound';
    $this->data['text'] = '';
  }
  
  public function  ServerHeader() {
    return "<?php
    @Header( 'HTTP/1.0 404 Not Found');
    @Header( 'Content-Type: text/html; charset=utf-8' );
    @Header( 'Cache-Control: no-cache, must-revalidate');
    @Header( 'Pragma: no-cache');
    ?>";
  }
  
  function GetTemplateContent() {
    $this->CacheEnabled = false;
$result = $this->text != '') ? $this->text :  '<h2 class="center">'. tlocal::$data['default']['notfound'] . '</h2>';
$theme = ttheme::instance();
return sprintf($theme->simplecontent, $result);
  }

}

?>