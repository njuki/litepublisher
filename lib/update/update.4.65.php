<?php

function update465() {
litepublisher::$site->jqueryui_version = '1.8.14';
$template = ttemplate::instance();
$template->heads = str_replace('1.8.11', '$site.jqueryui_version', $template->heads);
$template->save();

$admin = tadminmenus::instance();
$admin->heads = str_replace('1.8.11', '$site.jqueryui_version', $admin->heads );
$admin->save();
}

}
