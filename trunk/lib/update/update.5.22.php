<?php

function update522() {
unset(litepublisher::$classes->items['toauth']);
litepublisher::$classes->save();

if (litepublisher::$classes->exists('tembeddedplayers')) {
$p = tembeddedplayers::i();
$p->video  = str_replace('flowplayer-3.2.7.swf', 'flowplayer-3.2.8.swf', $p->video);
$p->save();
}
}