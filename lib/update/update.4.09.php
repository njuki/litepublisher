<?php

function update409() {
$admin = tadminmenus::instance();
  $admin->heads = '<script type="text/javascript" src="$site.files/js/litepublisher/admin.js"></script>';
$admin->save();

litepublisher::$site->jquery_version = '1.4.4';
litepublisher::$site->save();

$template = ttemplate::instance();
$template->heads = str_replace('litepublisher.min.js', 'litepublisher.$site.jquery_version.min.js', $template->heads);
$template->save();
}