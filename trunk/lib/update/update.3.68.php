<?php
function update368() {
if (isset(litepublisher::$classes->items['Tdomrss'])) unset(litepublisher::$classes->items['Tdomrss']);
litepublisher::$classes->items['tdomrss'] = array('domrss.class.php', '');
litepublisher::$classes->items['tnode'] = array('domrss.class.php', '');
litepublisher::$classes->items['tclasses'] = array('classes.class.php', '');
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