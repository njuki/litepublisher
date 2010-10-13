<?php
function update397() {
$filter = tcontentfilter::instance();
if (!isset($filter->data['autolinks'])) {
$filter->data['autolinks'] = true;
$filter->data['commentautolinks'] = true;
$filter->save();
}

if (isset(litepublisher::$classes->items['texternallinks'])) {
$plugin = texternallinks::instance();
  $filter = tcontentfilter::instance();
  $filter->onaftercomment = $plugin->filter;
}
}
?>