<?php

class TAdminIndex  extends TAdminPage {
 
 public static function &Instance() {
  return GetInstance(__class__);
 }
 
 protected function CreateData() {
  parent::CreateData();
  $this->basename = 'index';
 }
 
 public function Install() {
  global $paths;
  @mkdir($paths['data'] . 'admin', 0777);
  @chmod($paths['data'] . 'admin', 0777);
  $urlmap = &TUrlmap::Instance();
  $urlmap->AddNode('admin', get_class($this), null);
 }
 
 public function Uninstall() {
  global $paths;
  TUrlmap::unsub($this);
  TFiler::DeleteFiles($paths['data'] . 'admin', true, true);
 }
 
 public function Getcontent() {
  $editor = &TPostEditor::Instance();
  return $editor->Getcontent();
 }
 
}//class
?>