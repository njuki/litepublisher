<?php
function TXMLRPCWordpressInstall(&$self) {
 $Caller = &TXMLRPC::Instance();
 $Caller->Lock();
 // WordPress API
 $Caller->Add('wp.getPage',		'wp_getPage', get_class($self));
 $Caller->Add('wp.getPages',		'wp_getPages', get_class($self));
 $Caller->Add('wp.deletePage',		'wp_deletePage', get_class($self));
 $Caller->Add('wp.getPageList',	'wp_getPageList', get_class($self));
 $Caller->Add('wp.newCategory',		'wp_newCategory', get_class($self));
 
 $Caller->Unlock();
}

?>