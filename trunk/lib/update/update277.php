<?php
function Update277() {
global $paths, $Options, $classes;
$classes->items['ITemplate'] = array('interfaces.php', '');
$classes->items['TXMLRPCAbstract'] = array('xmlrpc-abstractclass.php', '');
$classes->save();

$Options->lock();
$Options->files = $Options->url;
$Options->version = '2.77';
$Options->unlock();

$filename = $paths['data'] . 'template.pda.php';
$d = unserialize(PHPUncomment(file_get_contents($filename)));
$d['widgets'] = array();
$d['sitebars'] = array();
file_put_contents($filename, PHPComment(serialize($d)));
$urlmap = TUrlmap::Instance();
$urlmap->ClearCache();
$urlmap->Redir301('/admin/service/?update=1');

}
?>