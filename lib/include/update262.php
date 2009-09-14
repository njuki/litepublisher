<?php

function Update262() {
global $paths, $classes;
@unlink($paths['plugins'] . 'sape' . DIRECTORY_SEPARATOR . 'blogolet.ru.links.db');
$classes->Add('Tdomrss', 'domrss.php');

$sitemap =TSitemap::Instance();
$sitemap->Data['items'] = array();
$sitemap->Save();
}

?>