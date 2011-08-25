<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class trssfilelist extends tplugin {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function beforepost($id, &$content) {
    $post = tpost::instance($id);
if (count($post->files) > 0) {
$theme = $post->theme;
$image = $theme->templates['content.post.filelist.image'];
$theme->templates['content.post.filelist.image'] = str_replace('href="$link"', 
'href="$site.url/rssfilelist.htm?urlpost=$post.url&urlfile=$url"', $image);
    $content .= $post->filelist;
$theme->templates['content.post.filelist.image'] = $image;
}
  }

public function request($arg) {
$urlpost = $_GET['urlpost'];
$urlfile = $_GET['urlfile'];
if (($idpost ==0) || ($idfile == 0)) return  404;
    setcookie('rssfilelist', $urlfile, time() + 3600, litepublisher::$site->subdir . '/', false);
return turlmap::redir301($post->url);
}

}//class