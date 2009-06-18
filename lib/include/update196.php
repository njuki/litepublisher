<?php

function Update196() {
 global $Options, $Urlmap, $paths;
 $Options->fromemail = 'litepublisher@' . $_SERVER['SERVER_NAME'];
 
 $admin = &TAdminModerator::Instance();
 unset($admin->Data['fromemail']);
 unset($admin->Data['SendNotification']);
 $admin->Save();
 @unlink($paths['data'] . 'admin' . DIRECTORY_SEPARATOR  . 'moderator.php');
 
 $CommentManager = &TCommentManager::Instance();
 $CommentManager->Lock();
 $CommentManager->UnsubscribeClass($admin);
 $CommentManager->Data['SendNotification'] =  true;
 $CommentManager->Unlock();
}

?>