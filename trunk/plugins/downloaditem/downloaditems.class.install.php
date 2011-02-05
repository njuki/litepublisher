<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tdownloaditemsInstall($self) {
  if (!dbversion) die("Downloads require database");
  $dir = dirname(__file__) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;
  $manager = tdbmanager ::instance();
  $manager->CreateTable($self->childtable, file_get_contents($dir .'downloaditem.sql'));
  
  $optimizer = tdboptimizer::instance();
  $optimizer->lock();
  $optimizer->childtables[] = 'downloaditems';
  $optimizer->addevent('postsdeleted', get_class($self), 'postsdeleted');
  $optimizer->unlock();

  tlocal::loadsection('admin', 'downloaditems', $dir);
    tlocal::loadsection('', 'downloaditem', dirname(__file__) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR);
  $ini = parse_ini_file($dir . litepublisher::$options->language . '.install.ini', false);

$tags = ttags::instance();
litepublisher::$options->downloaditem_themetag = $tags->add(0, $ini['themetag']);
litepublisher::$options->downloaditem_plugintag = $tags->add(0, $ini['plugintag']);
$base = basename(dirname(__file__));
$classes = litepublisher::$classes;
  $classes->lock();
/*
  //install polls if its needed
  $plugins = tplugins::instance();
  if (!isset($plugins->items['polls'])) $plugins->add('polls');
  $polls = tpolls::instance();
  $polls->garbage = false;
  $polls->save();
  */

  $classes->Add('tdownloaditem', 'downloaditem.class.php', $base);
  tdownloaditem::checklang();
  $classes->Add('tdownloaditemsmenu', 'downloaditems.menu.class.php', $base);
  $classes->Add('tdownloaditemeteditor', 'admin.downloaditem.editor.class.php',$base);
  $classes->Add('tadmindownloaditems', 'admin.downloaditems.class.php', $base);
  $classes->Add('tdownloaditemcounter', 'downloaditem.counter.class.php', $base);
  $classes->Add('taboutparser', 'about.parser.class.php', $base);
  $classes->unlock();

$lang = tlocal::instance('downloaditems');
  $adminmenus = tadminmenus::instance();
  $adminmenus->lock();
  $parent = $adminmenus->createitem(0, 'downloaditems', 'author', 'tadmindownloaditems');
  $adminmenus->items[$parent]['title'] = $lang->downloaditems;
  
  $idmenu = $adminmenus->createitem($parent, 'theme', 'author', 'tadmindownloaditems');
  $adminmenus->items[$idmenu]['title'] = $lang->themes;
  
  $idmenu = $adminmenus->createitem($parent, 'plugin', 'author', 'tadmindownloaditems');
  $adminmenus->items[$idmenu]['title'] = $lang->plugins;
  
  $idmenu = $adminmenus->createitem($parent, 'editor', 'author', 'tdownloaditemeditor');
  $adminmenus->items[$idmenu]['title'] = $lang->add;

  $idmenu = $adminmenus->createitem($parent, 'addurl', 'author', 'tadmindownloaditems');
  $adminmenus->items[$idmenu]['title'] = $lang->addurl;
  
  $adminmenus->unlock();
  
  $menus = tmenus::instance();
  $menus->lock();
  $menu = tdownloaditemsmenu::instance();
  $menu->type = '';
  $menu->url = '/downloads.htm';
  $menu->title = $ini['downloads'];
  $menu->content = '';
  $id = $menus->add($menu);
  
  foreach (array('theme', 'plugin') as $type) {
    $menu = tdownloaditemsmenu::instance();
    $menu->type = $type;
    $menu->parent = $id;
    $menu->url = sprintf('/downloads/%ss.htm', $type);
    $menu->title = $lang->$type;
    $menu->content = '';
    $menus->add($menu);
  }
  $menus->unlock();

$template = ttemplate::instance();
$template->addtohead(getd_download_js());

  $parser = tthemeparser::instance();
  $parser->parsed = $self->themeparsed;
  ttheme::clearcache();
  
  $linkgen = tlinkgenerator::instance();
  $linkgen->data['downloaditem'] = '/[type]/[title].htm';
  $linkgen->save();
  litepublisher::$options->savemodified();
}

function tdownloaditemsUninstall($self) {
  //die("Warning! You can lost all downloaditems!");
  tposts::unsub($self);
  
  $adminmenus = tadminmenus::instance();
  $adminmenus->deletetree($adminmenus->url2id('/admin/downloaditems/'));
  
  $menus = tmenus::instance();
  $menus->deletetree($menus->class2id('tdownloaditemsmenu'));

  $parser = tthemeparser::instance();
  $parser->unsubscribeclass($self);
  ttheme::clearcache();


$classes = litepublisher::$classes;
  $classes->lock();
  $classes->delete('tdownloaditem');
  $classes->delete('tdownloaditemsmenu');
  $classes->delete('tdownloaditemeteditor');
  $classes->delete('tadmindownloaditems');
  $classes->delete('tdownloaditemcounter');
  $classes->delete('taboutparser');
  $classes->unlock();

/*  
  if (class_exists('tpolls')) {
    $polls = tpolls::instance();
    $polls->garbage = true;
    $polls->save();
  }
*/

  tlocal::clearcache();
  
  $manager = tdbmanager ::instance();
  $manager->deletetable($self->childtable);
  
  $optimizer = tdboptimizer::instance();
  $optimizer->lock();
  $optimizer->unsubscribeclass($self);
  if (false !== ($i = array_search('downloaditems', $optimizer->childtables))) {
    unset($optimizer->childtables[$i]);
  }
  $optimizer->unlock();

$template = ttemplate::instance();
$template->deletefromhead(getd_download_js());

litepublisher::$options->delete('downloaditem_themetag');
litepublisher::$options->delete('downloaditem_plugintag');
litepublisher::$options->savemodified();
}

function getd_download_js() {
$result ='<script type="text/javascript">';
$result .= "\n\$(document).ready(function() {\n";
$result .= "if (\$(\"a[rel='theme'], a[rel='plugin']\").length) {\n";
$result .= '$.getScript("$site.files/plugins/' . basename(dirname(__file__)) . "/downloaditem.js\");\n";
$result .= "}\n";
$result.= "});\n";
$result .= "</script>\n";
return $result;
}

function add_downloaditems_to_theme($theme) { 
if (empty($theme->templates['custom']['downloadexcerpt'])) {
  $dir = dirname(__file__) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;
    tlocal::loadsection('', 'downloaditem', $dir);
    tlocal::loadsection('admin', 'downloaditems', $dir);
     ttheme::$vars['lang'] = tlocal::instance('downloaditems');
$lang = tlocal::instance('downloaditems');
$theme->templates['custom']['downloadexcerpt'] = $theme->replacelang(file_get_contents($dir . 'downloadexcerpt.tml'), $lang);
$theme->templates['custom']['downloaditem'] = $theme->replacelang(file_get_contents($dir . 'downloaditem.tml'), $lang);
$theme->templates['custom']['siteform'] = $theme->parse(file_get_contents($dir . 'siteform.tml'));

//admin
$theme->templates['customadmin']['downloadexcerpt'] = array(
'type' => 'editor',
'title' => $lang->downloadexcerpt
);

$theme->templates['customadmin']['downloaditem'] = array(
'type' => 'editor',
'title' => $lang->downloadlinks
);

$theme->templates['customadmin']['siteform'] = array(
'type' => 'editor',
'title' => $lang->siteform
);
}
//var_dump($theme->templates['customadmin'], $theme->templates['custom']);
}
