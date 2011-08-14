<?php

function update485() {
  $template = ttemplate::instance();
  unset($template->data['hovermenu']);
$template->save();

$views = tviews::instance();
$views->lock();
foreach ($views->items as &$viewitem) {
$viewitem['hovermenu'] = true;
}
$views->unlock();

if (litepublisher::$classes->exists('ttidyfilter')) {
    $filter = tcontentfilter::instance();
    $filter->lock();
$filter->unsubscribeclassname('ttidyfilter');
$tidy = ttidyfilter::instance();
    $filter->onaftersimple = $tidy->filter;
    $filter->onaftercomment = $tidy->filter;
    $filter->unlock();
}

}