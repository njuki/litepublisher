<?php

function update519() {
litepublisher::$classes->items['tsitemap'][0] = dbversion ? 'sitemap.class.db.php' : 'sitemap.class.files.php';
litepublisher::$classes->save();
}