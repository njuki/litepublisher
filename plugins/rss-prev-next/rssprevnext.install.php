<?php

function TRSSPrevNextInstall(&$self) {$Template = &TTemplate::Instance();
  $rss = &TRSS::Instance();
  $rss->BeforePostContent = $self->BeforePostContent;
 }
 
function TRSSPrevNextUninstall(&$self) {
  $rss = &TRSS::Instance();
  $rss->UnsubscribeClass($self);
 }

?>