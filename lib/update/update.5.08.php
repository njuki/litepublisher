<?php

function update508() {
if (litepublisher::$classes->exists('tmobileplugin')) {
    $about = tplugins::getabout('mobile');
    $views = tviews::i();
if (!isset($views->defaults['mobile'])) {
 $views->defaults['mobile'] = $views->add($about['menutitle']);
}

    $idview =  $views->defaults['mobile'];
$view = tview::i($idview);
if ($view->themename != 'pda') {
$view->themename = 'pda';
$view->disableajax = true;
$view->save();
}
    
}
}