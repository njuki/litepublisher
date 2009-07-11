<?php

function TFoafManagerInstall(&$self) {
  $actions = &TXMLRPCOpenAction ::Instance();
  $actions->Lock();
  $actions->Add('friend.invate', get_class($self), 'Invate');
  $actions->Add('friend.reject', get_class($self), 'Reject');
  $actions->Add('friend.accept', get_class($self), 'Accept');
  $actions->Unlock();
  
  $cron = &TCron::Instance();
  $cron->Add('day', get_class($self), 'CheckFriendship', null);
}

function TFoafManagerUninstall(&$self) {
  TUrlmap::unsub($self);
  $actions = &TXMLRPCOpenAction ::Instance();
  $actions->DeleteClass(get_class($self));
  $cron = &TCron::Instance();
  $cron->RemoveClass(get_class($self));
}

?>