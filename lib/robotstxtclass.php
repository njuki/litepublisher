<?php

class TRobotstxt extends TItems {
 public function GetBaseName() {
  return 'robots.txt';
 }
 
 public static function &Instance() {
  return GetInstance(__class__);
 }
 
 
 public function AddDisallow($url) {
  return $this->Add("Disallow: $url");
 }
 
 public function Add($value) {
  if (!in_array($value, $this->items)) {
   $this->items[] = $value;
   $this->Save();
   $Urlmap = &TUrlmap::Instance();
   $Urlmap->SetExpired('/robots.txt');
   $this->Added($value);
  }
 }
 
 public function Request($param) {
  $s = "<?php
  @header('Content-Type: text/plain');
  ?>";
  $s .= implode("\n", $this->items);
  return  $s;
 }
 
}//class

?>