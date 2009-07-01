<?php

class THomepageInvert extends THomepage {
 
 public static function &Instance() {
  return GetInstance(__class__);
 }

 public function GetItems() { 
  global $Options, $Urlmap;
  $posts = &TPosts::Instance();
  $arch = array_reverse(array_keys($posts->archives));
  $Count = count($arch);
  $From = ($Urlmap->pagenumber - 1) * $Options->postsperpage;
  if ($From > $Count)  return array();
  $To = min($From + $Options->postsperpage, $Count);
return array_slice($arch, $From, $To - $From);
}

public function Install() {
 $Urlmap = &TUrlmap::Instance();
 $Urlmap->items['/']['class'] = get_class($this);
$Urlmap->Save();
 }

public function Uninstall() {
 $Urlmap = &TUrlmap::Instance();
 $Urlmap->items['/']['class'] = get_parent_class($this);
$Urlmap->Save();
}

}//class
?>
