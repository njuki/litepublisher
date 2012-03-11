<?php

function update519() {
$auth = tauthdigest::i();
litepublisher::$options->xxxcheck = $auth->xxxcheck;
    unset($auth->data['xxxcheck']);
$auth->save();

litepublisher::$classes->items['tauthdigest'][0] = 'authdigest.class.php';
unset(litepublisher::$classes->items['tauthdigest'][2]);

litepublisher::$classes->items['tguard'] = array('kernel.templates.php', '', 'guard.class.php');

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