<?php

function TFilesInstall(&$self) {
 $Urlmap = &TUrlmap::Instance();
 $Urlmap->AddGet('/download.php', get_class($self), null);
}

function TFilesUninstall(&$self) {
 TUrlmap::unsub($self);
}

?>