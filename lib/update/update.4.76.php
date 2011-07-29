<?php

function update476() {
if (litepublisher::$classes->exists('tbackup2dropbox')) {
$dropbox = tbackup2dropbox::instance();
    $dropbox->data['useshell'] = false;
$dropbox->save();
}