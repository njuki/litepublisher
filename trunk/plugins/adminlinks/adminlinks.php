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
  global $options, $template, $urlmap;
if ($index > 0 || $urlmap->adminpanel) return;
$theme = ttheme::instance();
$tml = $theme->getwidgetitem('widget', $index);
    tlocal::loadlang('admin');

switch (get_class($template->context)) {
case 'tpost':
$post = $template->context;
 $lang = tlocal::instance('posts');
$title = $lang->adminpost;
$editurl = $options->url . "/admin/posts/editor/" . $options->q . "id=$post->id";
$action = $options->url . "/admin/posts/" . $options->q . "id=$post->id&action";
$links = sprintf($tml, $options->url . "/admin/posts/editor/" . $options->q . "mode=short", tlocal::$data['names']['quick']);
$links .= sprintf($tml, "$editurl&mode=short", $lang->edit);
$links .= sprintf($tml, "$editurl&mode=midle", $lang->midledit);
$links .= sprintf($tml, "$editurl&mode=full", $lang->fulledit);
$links .= sprintf($tml, "$editurl&mode=update", $lang->updatepost);
$links .= sprintf($tml, "$action=delete", $lang->delete);
break;

case 'tcategories':
case 'ttags':
$tags = $template->context;
$name = $tags instanceof ttags ? 'tags' : 'categories';
$adminurl = $options->url . "/admin/posts/$name/";
$lang = tlocal::instance('tags');
$title = $lang->{$name};
$links = sprintf($tml,$adminurl, $lang->add);
$adminurl .= $options->q . "id=$tags->id";
$links .= sprintf($tml,$adminurl, $lang->edit);
$links .= sprintf($tml, "$adminurl&action=delete", $lang->delete);
$links .= sprintf($tml, "$adminurl&full=1", $lang->fulledit);
break;

case 'thomepage':
$lang = tlocal::instance('options');
$title = $lang->home;
$links = sprintf($tml, $options->url . "/admin/options/home/", $lang->title);
$links .= sprintf($tml, $options->url . "/admin/widgets/homepagewidgets/", tlocal::$data['names']['homepagewidgets']);
break;

default:
return;
}

$links .= sprintf($tml, $options->url . "/admin/logout/", tlocal::$data['login']['logout']);
$widget = $theme->getwidget($title, $links, 'widget', $index);
$content = $widget . $content;
}

}//class

?>