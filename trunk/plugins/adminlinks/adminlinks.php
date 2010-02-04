<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class tadminlinksplugin extends tplugin {
 
 public static function instance() {
  return getinstance(__class__);
 }

 public function onsitebar(&$content, $index) {
  global $Options, $template;
if ($index > 0) return;
if ($template->context instanceof tpost) {
$post = $template->context;
$theme = ttheme::instance();
$tml = $theme->getwidgetitem('widget', $index);
    tlocal::loadlang('admin');
 $lang = tlocal::instance('posts);
$editurl = $options->url . "/admin/posts/editor/" . $options->q . "id=$post->id";
$links = sprintf($tml, "$editurl&mode=short", $lang->edit);
$links .= sprintf($tml, "$editurl&mode=midle", $lang->midledit);
$links .= sprintf($tml, "$editurl&mode=full", $lang->fulledit);
$links .= sprintf($tml, "$editurl&action=delete", $lang->delete);
$links .= sprintf($tml, $options->url . "/admin/logout/", tlocal::$data['login']['logout']);

$widget = $theme->getwidget($lang->adminpost, $links, 'widget', $index);
$content = $widget . $content;
return true;
}

if ($template->context instanceof tcategories) {

$content = $widget . $content;
return;
}

}

}//class

?>