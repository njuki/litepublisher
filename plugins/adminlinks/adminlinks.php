<?php

class TAdminLinksPlugin extends TPlugin {
 
 public static function &Instance() {
  return GetInstance(__class__);
 }

 public function postscript() {
  global $Options, $post;
  if (is_a($post, 'TPost')) {
  $lang = &TLocal::Instance();
$lang->section = 'default';
//<a href='$Options->url/admin/posteditor/full/{$Options->q}postid=$post->id'>&bull;$lang->fulledit</a>
$adminlinks = "<p><a href=\"$Options->url/admin/posteditor/{$Options->q}postid=$post->id\">&bull;$lang->edit</a>
<a href=\"$Options->url/admin/posts/{$Options->q}action=delete&postid=$post->id\">&bull;$lang->delete</a></p>\n";

$pt = &TTemplatePost::Instance();
    $pt->ps = $adminlinks . $pt->ps;
   }
}

}//class

?>