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
}