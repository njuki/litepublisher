<?php
function update398() {
if (isset(litepublisher::$classes->items['tmarkdownplugin'])) {
$plugin = tmarkdownplugin::instance();
  $filter = tcontentfilter::instance();
  $filter->oncomment= $plugin->filter;
}
}
?>