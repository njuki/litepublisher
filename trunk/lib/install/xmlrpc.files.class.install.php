<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function TXMLRPCFilesInstall($self) {
  $caller = TXMLRPC::instance();
  $caller->lock();
  
  $caller->add('litepublisher.deletefile',		'delete', get_class($self));
  $caller->add('litepublisher.files.getbrowser',		'getbrowser', get_class($self));
  $caller->add('litepublisher.files.getpage',		'getpage', get_class($self));
  $caller->add('litepublisher.files.geticons',		'geticons', get_class($self));
  $caller->add('litepublisher.files.getthemes',		'getthemes', get_class($self));
  $caller->add('litepublisher.files.gettags',		'gettags', get_class($self));
  $caller->unlock();
  
  //swupload
  $urlmap = turlmap::instance();
  $urlmap->add('/admin/swfupload.htm', get_class($self), null, 'normal');
}

?>