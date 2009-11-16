<?php

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