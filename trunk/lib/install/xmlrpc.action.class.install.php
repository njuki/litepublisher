<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function TXMLRPCActionInstall($self) {
  $caller = TXMLRPC::instance();
  $caller->lock();
  $caller->add('litepublisher.action.send', 'send', get_class($self));
  $caller->add('litepublisher.action.confirm', 'confirm', get_class($self));
  $caller->unlock();
}

function TXMLRPCActionUninstall($self) {
  $caller = TXMLRPC::instance();
  $caller->deleteclass(get_class($self));
}

?>