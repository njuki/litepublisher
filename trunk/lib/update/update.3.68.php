<?php
function update368() {
if (isset(litepublisher::$classes->items['Tdomrss'])) unset(litepublisher::$classes->items['Tdomrss']);
litepublisher::$classes->items['tdomrss'] = array('domrss.class.php', '');
litepublisher::$classes->items['tnode'] = array('domrss.class.php', '');
litepublisher::$classes->items['tclasses'] = array('classes.class.php', '');
litepublisher::$classes->save();
@unlink(litepublisher::$paths->lib . 'classes.php');

$template = ttemplate::instance();
$template->data['adminjavascripts'] = array();
    $template->data['stdjavascripts'] = array(
'hovermenu' => '/js/litepublisher/hovermenu.min.js',
'comments' => '/js/litepublisher/comments.min.js',
'moderate' => '/js/litepublisher/moderate.min.js'
);

$template->save();

tlocal::loadlang('admin');
$admin = tadminmenus::instance();
//$admin->deleteurl('/admin/themes/javascripts/');
$idthemes = $admin->url2id('/admin/themes/');
$admin->createitem($idthemes, 'javascripts', 'admin', 'tadminthemes');

    tfiler::deletemask(litepublisher::$paths->languages . '*.php');

    $dir =litepublisher::$paths->data . 'languages';
mkdir($dir, 0777);
chmod($dir, 0777);
}

?>