<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

function TCommentFormInstall(&$self) {
  global $Options;
  $url= '/send-comment.php';
  
  $Urlmap = &TUrlmap::Instance();
  $Urlmap->Add($url, get_class($self), null);
}

function TCommentFormUninstall(&$self) {
  TUrlmap::unsub($self);
}

?>