<?php

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