<?php

function update476() {
$updater = tupdater::instance();
$updater->data['useshell'] = false;
$updater->save();

if (litepublisher::$classes->exists('tbackup2dropbox')) {
$dropbox = tbackup2dropbox::instance();
    $dropbox->data['useshell'] = false;
    $dropbox->data['uploadfiles'] = false;
$dropbox->save();
}
}