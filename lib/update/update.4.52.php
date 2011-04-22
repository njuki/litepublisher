<?php

function update452() {
$admin = tadminmenus::instance();
$admin->heads = '  <link type="text/css" href="$site.files/js/jquery/ui-1.8.11/redmond/jquery-ui-1.8.11.custom.css" rel="stylesheet" />' .
str_replace('$site.files/js/litepublisher/admin.js', 
'$site.files/js/litepublisher/admin.$site.jquery_version.min.js', $admin->heads);
$admin->save();
}