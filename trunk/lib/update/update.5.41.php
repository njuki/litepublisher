<?php

function update541() {
$h = thomepage::i();
tposts::i()->addevent('changed', get_class($h), 'postschanged');
$h->postschanged();

if (litepublisher::$classes->exists('tregservices')) {
tregservices::i()->update_widget();
$css = tcssmerger::i();
$css->addstyle('/plugins/regservices/regservices.min.css');
}
}