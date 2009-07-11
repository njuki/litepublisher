<?php

function THomepageInstall(&$self) {
  global $Options;
  $Options->home = '/';
  $Urlmap = &TUrlmap::Instance();
  $Urlmap->Add($Options->home, get_class($self), null);
}

function THomepageUninstall(&$self) {
  TUrlmap::unsub($self);
}

?>