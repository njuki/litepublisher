<?php

function Update222() {
 global $paths;
 TClasses::Lock();
 
 TClasses::Register('THtmlResource', 'htmlresource.php');
 TClasses::Unregister('TAdminQuickPost');
 TClasses::Register('TPostEditor', 'adminposteditor.php');
 
 TClasses::Unlock();
 
 $links = &TLinksWidget::Instance();
 $links->Data['redir'] = false;
 $links->Save();
 
 $Urlmap = &TUrlmap::Instance();
 $Urlmap->AddGet($links->redirlink, get_class($links), null);
 
 @unlink($paths['lib']. 'adminquickpost.php');
}

?>