<?php

function update514() {
litepublisher::$classes->add('tcssmerger', 'cssmerger.class.php');
litepublisher::$classes->add('tadmincssmerger', 'admin.cssmerger.class.php');

tcssmerger::i()->install();
$template = ttemplate::i();
$template->deletefromhead('<link type="text/css" href="$site.files/js/prettyphoto/css/prettyPhoto.css" rel="stylesheet" />');
  $template->addtohead('<link type="text/css" href="$site.files$template.cssmerger_default" rel="stylesheet" />');

$lang = tlocal::admin('views');
    $menus = tadminmenus::i();
    $menus->createitem($menus->url2id('/admin/views/'),
'cssmerger', 'admin', 'tadmincssmerger');
$menus->save();

ttheme::clearcache();
}