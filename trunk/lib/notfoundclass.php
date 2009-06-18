<?php

class TNotFound404 extends TEventClass {
 
 public static function &Instance() {
  return GetInstance(__class__);
 }
 
 protected function CreateData() {
  parent::CreateData();
  $this->basename = 'notfound';
  $this->Data['text'] = '';
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
  if ($this->text != '') return $this->text;
  return 		'<h2 class="center">'. TLocal::$data['default']['notfound'] . '</h2>';
 }
}

?>