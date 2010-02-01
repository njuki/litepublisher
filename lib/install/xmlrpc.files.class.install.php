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
  
  $caller->add('litepublisher.files.delete',		'delete', get_class($self));
  $caller->add('litepublisher.files.getbrowser',		'getbrowser', get_class($self));
  $caller->add('litepublisher.files. getpage',		'getpage', get_class($self));

   $caller->unlock();
}

?>