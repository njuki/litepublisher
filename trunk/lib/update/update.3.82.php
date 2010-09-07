<?php
function update382() {
$posts = tposts::instance();
$posts->addrevision();
}
?>