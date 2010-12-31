<?php

function update409() {
$admin = tadminmenus::instance();
  $admin->heads = '<script type="text/javascript" src="$site.files/js/litepublisher/admin.js"></script>';
$admin->save();
}