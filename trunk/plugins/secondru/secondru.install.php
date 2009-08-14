<?php

function TSecondruInstall(&$self) {
global $Options, $paths;
$dir =       $paths['cache'] . 'ru' . DIRECTORY_SEPARATOR;
@mkdir($dir, 0777);
@chmod($dir, 066);
$dir .='pda' . DIRECTORY_SEPARATOR;
@mkdir($dir, 0777);
@chmod($dir, 066);

$Urlmap = TUrlmap::Instance();
$Urlmap->BeforeRequest = $self->BeforeRequest;

$Options->OnGetUrl = $self->Geturl;
}

function TSecondruUninstall(&$self) {
global $Options, $paths;
$Options->UnsubscribeClass($self);
TUrlmap::unsub($self);
TFiler::DeleteFiles($paths['cache'] . 'ru' . DIRECTORY_SEPARATOR, true, true);
}

?>