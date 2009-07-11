<?php

function TXMLRPCMetaWeblogInstall(&$self) {
  $Caller = &TXMLRPC::Instance();
  $Caller->Lock();
  // MetaWeblog API (with MT extensions to structs)
  $Caller->Add('metaWeblog.newPost', 'newPost', get_class($self));
  $Caller->Add('metaWeblog.editPost', 'editPost', get_class($self));
  $Caller->Add('metaWeblog.getPost', 'getPost', get_class($self));
  $Caller->Add('metaWeblog.getRecentPosts', 'getRecentPosts', get_class($self));
  $Caller->Add('metaWeblog.getCategories', 'getCategories', get_class($self));
  $Caller->Add('metaWeblog.newMediaObject', 'newMediaObject', get_class($self));
  
  // Aliases
  $Caller->Add('wp.getCategories',		'getCategories',	get_class($self));
  $Caller->Add('wp.uploadFile',		'newMediaObject',	get_class($self));
  
  //forward wordpress
  $Caller->Add('wp.newPage',	'wp_newPage', get_class($self));
  $Caller->Add('wp.editPage',	'wp_editPage', get_class($self));
  
  $Caller->Unlock();
}

?>