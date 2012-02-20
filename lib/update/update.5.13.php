<?php

function update513() {
litepublisher::$options->show_file_perm = false;
litepublisher::$classes->add('tprivatefiles', 'files.private.class.php');

$p = tpasswordpage::i();
$p->form = str_replace(']', '', $p->form);
$p->save();

$files = tfiles::i();
if (dbversion) {
$man = tdbmanager::i();
$man->alter($files->table, "drop bitrate"); 
$man->alter($files->table, "drop framerate"); 
$man->alter($files->table, "drop samplingrate"); 
$man->alter($files->table, "drop channels"); 
$man->alter($files->table, "drop duration"); 
$man->alter($files->table, "add   `idperm` int unsigned NOT NULL default '0' after author");
} else {
foreach ($files->items as &$item) {
$item['idperm'] = 0;
unset($item['bitrate']);
unset($item['framerate']);
unset($item['samplingrate']);
unset($item['channels']);
unset($item['duration']);
}
$files->save();
}

}