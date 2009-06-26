<?php

class TCustomWidget extends TItems {
 
 public static function &Instance() {
  return GetInstance(__class__);
 }
 
 protected function CreateData() {
  parent::CreateData();
  $this->basename   = 'customwidget';
 }
 
 public function GetWidgetContent($id) {
  if (!$this->items[$id]['templ']) return $this->items[$id]['content'];
  global $Options, $Template;
  $result = $Template->GetBeforeWidget('before', $this->items[$id]['title']);
  $result .= $this->items[$id]['content'];
  $result .= $Template->GetAfterWidget();
  return $result;
 }
 
 public function Add($title, $content, $templ) {
  $Template = &TTemplate::Instance();
  $id = $Template->AddWidget(get_class($this), 'echo');
  $this->items[$id] = array(
  'title' => $title,
  'content' => $content,
  'templ' => $templ
  );
  
  $this->Save();
  $this->Added($id);
  return $id;
 }
 
 public function Edit($id, $title, $content, $templ) {
  $this->items[$id] = array(
  'title' => $title,
  'content' => $content,
  'templ' => $templ
  );
  
  $this->Save();
  TTemplate::WidgetExpired($this);
 }
 
 public function Delete($id) {
  if (isset($this->items[$id])) {
   unset($this->items[$id]);
   $this->Save();
   
   $Template = &TTemplate::Instance();
   $Template->DeleteIdWidget($id);
   $this->Deleted($id);
  }
 }
 
 public function WidgetDeleted($id) {
  if (isset($this->items[$id])) {
   unset($this->items[$id]);
   $this->Save();
  }
 }
} //class
?>