<?php
function update376() {
$classes = litepublisher::$classes;
$classes->lock();
$classes->items['

$classes->unlock();


$admin = tadminmenus::instance();
$admin->deleteurl('/admin/options/openid/');
@unlink(litepublisher::$paths->libinclude . 'bigmath.php');
@unlink(litepublisher::$paths->lib . 'openid.class.php');
}
?>