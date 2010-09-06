<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tyoutubeuploaderInstall($self) {
  $plugins = tplugins::instance();
  if (!isset($plugins->items['youtube-feed'])) $plugins->add('youtube-feed');
$dir = litepublisher::$paths->data . 'youtube';
if (!file_exists($dir)) mkdir($dir, 0777);
  @chmod($dir, 0777);

  $name = tplugins::getname(__file__);
  $classes = litepublisher::$classes;
  $classes->lock();
  $classes->add('tyoutubecategories', 'youtube.class.install.php', $name););
  $classes->unlock();

$urlmap = turlmap::instance();
$urlmap->lock();
$urlmap->add('/admin/youtube/getrequest.htm', get_class($self), 'request', 'get');
$urlmap->add('/admin/youtube/accesstoken.htm', get_class($self), 'access', 'get');
$urlmap->add('/admin/youtube/uploaded.htm', get_class($self), 'uploaded', 'get');
$urlmap->unlock();
  
  $rpc = TXMLRPC::instance();
  $rpc->add('litepublisher.youtube.getuploadtoken', 'xmlrpcgetuploadtoken', get_class($self));
}

function tyoutubeuploaderUninstall($self) {
  $rpc = TXMLRPC::instance();
  $rpc->deleteclass(get_class($self));
  
  tfiler::delete(litepublisher::$paths->data . 'youtube', false, true);
turlmap::unsub($self);

  $classes = litepublisher::$classes;
$classes->lock();
$classes->delete('tyoutubecategories');
$classes->unlock();
}

function tyoutubecategoriesInstall($self) {
  $self->update();
}

?>