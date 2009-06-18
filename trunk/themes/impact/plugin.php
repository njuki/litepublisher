<?php

class TImpact extends TPlugin {
 //public $color;
 //public $links;
 
 public static function &Instance() {
  return GetInstance(__class__);
 }
 
 public function CreateData() {
  parent::CreateData();
  $this->Data['color'] = '#8A95B7';
  $this->Data['links'] = '#3366CC';
 }
 
 public function Install() {
  $Template = &TTemplate::Instance();
  $Template->Lock();
  $Template->AddTag('ImpactColor',   get_class($this), 'GetImpactColor');
  $Template->AddTag('ImpactLinks', get_class($this), 'GetImpactLinks');
  $Template->Unlock();
 }
 
 public function Uninstall() {
  $Template = &TTemplate::Instance();
  $Template->DeleteTagClass(get_class($this));
 }
 
 public function GetImpactColor() {
  return $this->color;
 }
 
 public function GetImpactLinks() {
  return $this->links;
 }
 
}//class

?>