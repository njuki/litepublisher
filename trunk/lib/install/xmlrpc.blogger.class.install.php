<?php

function TXMLRPCBloggerInstall(&$self) {
  $Caller = &TXMLRPC::Instance();
  $Caller->Lock();
  
  // Blogger API
  $Caller->Add('blogger.getUsersBlogs', 'getUsersBlogs', get_class($self));
  $Caller->Add('blogger.getUserInfo', 'getUserInfo', get_class($self));
  $Caller->Add('blogger.getPost', 'getPost', get_class($self));
  $Caller->Add('blogger.getRecentPosts', 'getRecentPosts', get_class($self));
  $Caller->Add('blogger.getTemplate', 'getTemplate', get_class($self));
  $Caller->Add('blogger.setTemplate', 'setTemplate', get_class($self));
  $Caller->Add('blogger.newPost', 'newPost', get_class($self));
  $Caller->Add('blogger.editPost', 'editPost', get_class($self));
  $Caller->Add('blogger.deletePost', 'deletePost', get_class($self));
  
  // MetaWeblog API aliases for Blogger API
  // see http://www.xmlrpc.com/stories/storyReader$2460
  $Caller->Add('metaWeblog.deletePost', 'deletePost', get_class($self));
  $Caller->Add('metaWeblog.getTemplate', 'getTemplate', get_class($self));
  $Caller->Add('metaWeblog.setTemplate', 'setTemplate', get_class($self));
  $Caller->Add('metaWeblog.getUsersBlogs', 'getUsersBlogs', get_class($self));
  
  $Caller->Unlock();
}

?>