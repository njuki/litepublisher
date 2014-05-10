<?php
function update584() {
  litepublisher::$site->jquery_version = '1.11.1';

$man = tdbmanager::i();
$man->addenum('files', 'media', 'flash');
$man->addenum('files', 'mime', 'application/x-shockwave-flash');
}