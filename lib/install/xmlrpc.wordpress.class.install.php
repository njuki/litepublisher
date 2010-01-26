<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function TXMLRPCWordpressInstall($self) {
  $`caller = TXMLRPC::instance();
  $caller->lock();
  // WordPress API
  $caller->add('wp.getPage',		'wp_getPage', get_class($self));
  $caller->add('wp.getPages',		'wp_getPages', get_class($self));
  $caller->add('wp.deletePage',		'wp_deletePage', get_class($self));
  $caller->add('wp.getPageList',	'wp_getPageList', get_class($self));
  $caller->add('wp.newCategory',		'wp_newCategory', get_class($self));
  $caller->add('wp.deleteCategory ',		'deleteCategory ', get_class($self));
  
  $caller->unlock();
}

?>