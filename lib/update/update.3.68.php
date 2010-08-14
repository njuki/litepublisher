<?php
function update368() {
litepublisher::$classes->items['tdomrss'] = litepublisher::$classes->items['Tdomrss'];
unset(litepublisher::$classes->items['Tdomrss']);
litepublisher::$classes->save();

$template = ttemplate::instance();
$template->data['adminjavascripts'] = array();
$template->save();
}
?>