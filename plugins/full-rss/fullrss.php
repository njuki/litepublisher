<?php

class TFullRSS extends TPlugin {
 
 public static function &Instance() {
  return GetInstance(__class__);
 }
 
 public function AfterSetPostContent($id) {
$post = TPost::Instance($id);
$post->rss = $post->outputcontent;
}

function Install() {$Template = &TTemplate::Instance();
$filter = TContentFilter ::Instance();
$filter->AfterSetPostContent = $this->AfterSetPostContent;
 }
 
function Uninstall() {
$filter = TContentFilter ::Instance();
  $filter->UnsubscribeClass($this);
 }

}
?>