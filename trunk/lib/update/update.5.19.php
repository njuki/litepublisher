<?php

function update519() {
litepublisher::$classes->items['tsitemap'][0] = dbversion ? 'sitemap.class.db.php' : 'sitemap.class.files.php';
litepublisher::$classes->save();

if (dbversion) {
$sitemap = tsitemap::i();
$sitemap->data['classes'] = array(
'tpost' => 'posts',
'tcategories' => 'categories',
'ttags' => 'tags',
'tarchives', 
);

$sitemap->save();
}
}