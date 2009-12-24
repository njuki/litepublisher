<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function TXMLRPCMovableTypeInstall(&$self) {
  $Caller = &TXMLRPC::Instance();
  $Caller->Lock();
  
  // MovableType API
  $Caller->Add('mt.getCategoryList' , 'getCategoryList', get_class($self));
  $Caller->Add('mt.getRecentPostTitles' , 'getRecentPostTitles', get_class($self));
  $Caller->Add('mt.getPostCategories' , 'getPostCategories', get_class($self));
  $Caller->Add('mt.setPostCategories' , 'setPostCategories', get_class($self));
  $Caller->Add('mt.supportedMethods' , 'supportedMethods', get_class($self));
  $Caller->Add('mt.supportedTextFilters' , 'supportedTextFilters', get_class($self));
  $Caller->Add('mt.getTrackbackPings' , 'getTrackbackPings', get_class($self));
  $Caller->Add('mt.publishPost' , 'publishPost', get_class($self));
  
  $Caller->Unlock();
}

?>