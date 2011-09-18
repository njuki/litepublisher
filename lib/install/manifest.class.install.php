<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function TManifestInstall(&$self) {
  $Urlmap = TUrlmap::i();
  $Urlmap->Lock();
  $Urlmap->Add('/wlwmanifest.xml', get_class($self), 'manifest');
  $Urlmap->Add('/rsd.xml', get_class($self), 'rsd');
  $Urlmap->Unlock();
}

function TManifestUninstall(&$self) {
  TUrlmap::unsub($self);
}

?>