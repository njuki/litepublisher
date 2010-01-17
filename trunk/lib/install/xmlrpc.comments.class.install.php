<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function TXMLRPCComments($self) {
  $caller = TXMLRPC::instance();
  $caller->lock();

  $caller->add('litepublisher.deletecomment',		'delete', get_class($self));
  $caller->add('litepublisher.setcommentstatus',		'setstatus', get_class($self));
  $caller->add('litepublisher.addcomment',		'add', get_class($self));
  $caller->add('litepublisher.getcomment',	'getcomment', get_class($self));
  $caller->add('litepublisher.getrecentcomments',		'getrecent', get_class($self));
  $caller->add('litepublisher.moderate',		'moderate', get_class($self));
  
  $caller->unlock();
}

?>