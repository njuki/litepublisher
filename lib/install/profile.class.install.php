<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

function TProfileInstall(&$self) {
  $Urlmap = &TUrlmap::Instance();
  $Urlmap->Add('/profile/', get_class($self), null);
}

function TProfileUninstall(&$self) {
  TUrlmap::unsub($self);
}

?>