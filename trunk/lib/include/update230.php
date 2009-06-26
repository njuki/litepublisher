<?php

function Update230() {
 global $paths;
if (isset(TClasses::$items['TSapePlugin'])) {
$plugin = &TSapePlugin::Instance();
$plugin->Data['optimize'] = false;
$plugin->Data['optcode'] = '';
$plugin->Save();
}

 @unlink($paths['plugins']. 'sape'. DIRECTORY_SEPARATOR . 'blogolet.ru.links.db');
}

?>