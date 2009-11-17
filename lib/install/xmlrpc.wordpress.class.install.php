<?php
function TXMLRPCWordpressInstall(&$self) {
  $caller = TXMLRPC::instance();
  $caller->lock();
  // WordPress API
  $caller->add('wp.getPage',		'wp_getPage', get_class($self));
  $caller->add('wp.getPages',		'wp_getPages', get_class($self));
  $caller->add('wp.deletePage',		'wp_deletePage', get_class($self));
  $caller->add('wp.getPageList',	'wp_getPageList', get_class($self));
  $caller->add('wp.newCategory',		'wp_newCategory', get_class($self));
  
  $caller->unlock();
}

?>