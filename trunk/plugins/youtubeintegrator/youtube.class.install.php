<?php

functiontyoutubeInstall($self) {
  @mkdir(litepublisher::$paths->data . 'youtube', 0777)
  @chmod(litepublisher::$paths->data . 'youtube', 0777)
  $name = tplugins::getname(__file__);
  $classes = litepublisher::$classes;
  $classes->lock();
  $classes->add('tyoutubecategories', 'youtube.class.install.php', $name););
  $classes->add('tadminyoutubefiles', 'admin.files.youtube.class.php', $name);
  $classes->unlock();

$urlmap = turlmap::instance();
$urlmap->lock();
$urlmap->add('/admin/youtube/getrequest.htm', get_class($self), 'request', 'get');
$urlmap->add('/admin/youtube/accesstoken.htm', get_class($self), 'access', 'get');
$admin = tadminmenus::instance();
$idfiles = $admin->url2id('/admin/files/');
    $admin->createitem($idfiles, 'youtube', 'editor', 'tadminyoutubefiles');

$urlmap->unlock();
  
  $rpc = TXMLRPC::instance();
  $rpc->add('litepublisher.youtube.getuploadtoken', 'xmlrpcgetuploadtoken', get_class($self));
}

function tyoutubeUninstall($self) {
  $rpc = TXMLRPC::instance();
  $rpc->deleteclass(get_class($self));
  
  tfiler::delete(litepublisher::$paths->data . 'youtube', false, true);
  $urlmap = turlmap::instance();
$urlmap->lock();
turlmap::unsub($self);
$admin = tadminmenus::instance();
$admin->deleteurl('/admin/files/youtube/');

$urlmap->unlock();

  $classes = litepublisher::$classes;
$classes->lock();
$classes->delete('tyoutubecategories');
$classes->delete('tadminyoutubefiles');
$classes->unlock();
}

function tyoutubecategoriesInstall($self) {
  $self->update();
}

?>