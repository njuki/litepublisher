<?php

function update452() {
$admin = tadminmenus::instance();
$admin->heads = str_replace(
'<script type="text/javascript" src="$site.files/js/litepublisher/admin.js"></script>',
'  <link type="text/css" href="$site.files/js/jquery/ui-1.8.11/redmond/jquery-ui-1.8.11.custom.css" rel="stylesheet" />
<script type="text/javascript" src="$site.files/js/litepublisher/admin.$site.jquery_version.min.js"></script>',
$admin->heads);
$admin->save();
}