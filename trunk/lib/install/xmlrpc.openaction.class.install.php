<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function TXMLRPCOpenActionInstall(&$self) {
  $caller = TXMLRPC::instance();
  $caller->lock();
  $caller->add('openaction.send', 'send', get_class($self));
  $caller->add('openaction.confirm', 'confirm', get_class($self));
  $caller->unlock();
}

function TXMLRPCOpenActionUninstall($self) {
  $caller = TXMLRPC::instance();
  $caller->deleteclass(get_class($self));
}

?>