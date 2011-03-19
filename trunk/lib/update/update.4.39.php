<?php

function update439() {
if (!litepublisher::$classes->exists('tpolls'])) return;

$dir = litepublisher::$paths->plugins . 'polls' . DIRECTORY_SEPARATOR;
$p = tpolls::instance();
  $templates = parse_ini_file($dir . 'templates.ini',  true);
  $p->templateitems = $templates['item'];
  $p->templates = $templates['items'];
$p->save();

include_once($dir . 'polls.class.install.php');
$template = ttemplate::instance();
$template->addtohead(getpollhead());
$template->save();

$posts = tposts::instance();
$posts->addrevision();
tstorage::savemodified();
litepublisher::$urlmap->clearcache();
}