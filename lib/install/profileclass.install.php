<?php

function TProfileInstall(&$self) {
 $Urlmap = &TUrlmap::Instance();
 $Urlmap->Add('/profile/', get_class($self), null);
}

function TProfileUninstall(&$self) {
 TUrlmap::unsub($self);
}

?>