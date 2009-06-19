<?php

class THomepage extends TEventClass {
 
 protected function CreateData() {
  parent::CreateData();
  $this->basename = 'homepage' ;
  $this->Data['text'] = '';
  $this->Data['hideposts'] = false;
 }
 
 public static function &Instance() {
  return GetInstance(__class__);
 }
 
 public function GetTemplateContent() {
  global $Options, $Urlmap;
  $result = $this->text;
  if ($this->hideposts) return $result;
  $items =  $this->GetItems();
  $TemplatePost = &TTemplatePost::Instance();
  $result .= $TemplatePost->PrintPosts($items);
  $Posts = &TPosts::Instance();
  $result .=$TemplatePost->PrintNaviPages($Options->home, $Urlmap->pagenumber, ceil(count($Posts->archives)/ $Options->postsperpage));
  return $result;
 }
 
 public function GetItems() {
  global $Options, $Urlmap;
  $Posts = &TPosts::Instance();
  return $Posts->GetPublishedRange($Urlmap->pagenumber, $Options->postsperpage);
 }
 
 public function Settext($s) {
  global $Options;
  if ($this->text != $s) {
   $this->Data['text'] = $s;
   $this->Save();
   $urlmap = &TUrlmap::Instance();
   $urlmap->SetExpired($Options->home);
  }
 }
 
 public function Sethideposts($value) {
  global $Options;
  if ($this->hideposts != $value) {
   $this->Data['hideposts'] = $value;
   $this->Save();
   $urlmap = &TUrlmap::Instance();
   $urlmap->SetExpired($Options->home);
  }
 }
 
}

?>