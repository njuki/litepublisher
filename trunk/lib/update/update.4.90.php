<?php

function update490() {
litepublisher::$options->admincache = false;
litepublisher::$options->save();

if (litepublisher::$classes->exists('tpolls')) {
$p = tpolls::instance();
$dir = litepublisher::$paths->plugins . 'polls' . DIRECTORY_SEPARATOR;
  $templates = parse_ini_file($dir . 'templates.ini',  true);
  $about = tplugins::getabout('polls');
$theme = ttheme::instance();
tlocal::$data['polls'] = $about;
$lang = tlocal::instance('polls');
  $p->templates['microformat'] = $theme->replacelang($templates['microformat']['rate'], $lang);
$p->save();
}

if (litepublisher::$classes->exists('tprofile')) {
$profile = tprofile::instance();
$profile->data['skype'] = '';
$profile->data['googleprofile'] = '';
$profile->save();
}
}