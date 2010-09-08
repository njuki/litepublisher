<?php
function update382() {
$posts = tposts::instance();
$posts->addrevision();
ttheme::clearcache();

litepublisher::$options->ob_cache = false;
litepublisher::$options->compress = false;
}
?>