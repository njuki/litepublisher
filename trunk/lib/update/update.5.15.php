<?php

function update515() {
if (litepublisher::$classes->exists('tpagenator3000')) {
$merger = tcssmerger::i();
$merger->addstyle('/plugins/pagenator3000/paginator3000.css');
}

if (litepublisher::$classes->exists('tusernews')) {
$plugin = tusernews::i();
if (!isset($plugin->data['insertsource'])) {
$plugin->data['insertsource'] = true;
$plugin->save();
}
}

tadminmoderator::refilter();
}