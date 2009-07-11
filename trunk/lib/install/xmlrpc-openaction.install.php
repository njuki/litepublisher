<?php

function TXMLRPCOpenActionInstall(&$self) {
  $Caller = &TXMLRPC::Instance();
  $Caller->Lock();
  $Caller->Add('openaction.send', 'send', get_class($self));
  $Caller->Add('openaction.confirm', 'confirm', get_class($self));
  $Caller->Unlock();
}

function TXMLRPCOpenActionUninstall(&$self) {
  $Caller = &TXMLRPC::Instance();
  $Caller->RemoveClass(get_class($self));
}

?>