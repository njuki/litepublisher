<?php

function update439() {
$template = ttemplate::instance();
$template->data['js'] = '<script type="text/javascript" src="%s"></script>';
$template->data['jsready'] = '<script type="text/javascript">$(document).ready(function() {%s});</script>';
$template->data['jsload'] = '<script type="text/javascript">$.getScript(%s);</script>';
$template->save();
tstorage::savemodified();

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