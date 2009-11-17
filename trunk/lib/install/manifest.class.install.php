<?php

function TManifestInstall(&$self) {
  $Urlmap = TUrlmap::Instance();
  $Urlmap->Lock();
  $Urlmap->Add('/wlwmanifest.xml', get_class($self), 'manifest');
  $Urlmap->Add('/rsd.xml', get_class($self), 'rsd');
  $Urlmap->Unlock();
}

function TManifestUninstall(&$self) {
  TUrlmap::unsub($self);
}

?>