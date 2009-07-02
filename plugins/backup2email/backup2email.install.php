<?php

function TBackup2emailInstall(&$self) {$Template = &TTemplate::Instance();
$cron = &TCron::Instance();
$self->idcron = $cron->Add('hour', get_class($self), 'SendBackup', null);
$self->Save();
 }
 
function TBackup2emailUninstall(&$self) {
$cron = &TCron::Instance();
$cron->Remove($self->idcron);
 }

?>