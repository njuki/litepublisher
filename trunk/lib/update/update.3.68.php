<?php
function update368() {
litepublisher::$classes->items['tdomrss'] = litepublisher::$classes->items['Tdomrss'];
unset(litepublisher::$classes->items['Tdomrss']);
litepublisher::$classes->save();

$template = ttemplate::instance();
$template->data['adminjavascripts'] = array();
    $template->data['stdjavascripts'] = array(
'hovermenu' => '/js/litepublisher/hovermenu.min.js',
'comments' => '/js/litepublisher/comments.min.js',
'moderate' => '/js/litepublisher/moderate.min.js'
);

$template->save();
}
?>