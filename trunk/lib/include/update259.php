<?php

function Update259() {
$sitemap =TSitemap::Instance();
$sitemap->Data['items'] = array();
$sitemap->Save();
}
?>