<?php
function update376() {
$classes = litepublisher::$classes;
$classes->lock();
$classes->items['topenid'][1] = 'openid-provider';
$classes->unlock();

    $about = tplugins::getabout('openid-provider');
    extract($about, EXTR_SKIP);
$plugins = tplugins::instance();
$plugins->items['openid-provider'] =  array(
    'id' => ++$plugins->autoid,
    'class' => $classname,
    'file' => $filename,
    'adminclass' => $adminclassname,
    'adminfile' => $adminfilename
    );
$plugins->save();

$admin = tadminmenus::instance();
$admin->deleteurl('/admin/options/openid/');

@unlink(litepublisher::$paths->libinclude . 'bigmath.php');
@unlink(litepublisher::$paths->lib . 'openid.class.php');
}
?>