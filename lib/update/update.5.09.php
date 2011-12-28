<?php

function update509() {
litepublisher::$site->video_width =420;
litepublisher::$site->video_height = 300;

if (litepublisher::$classes->exists('tembeddedplayers')) {
$p = tembeddedplayers::i();
$p->video  = str_replace('flowplayer-3.2.5.swf', 'flowplayer-3.2.7.swf', $p->video);
$p->save();
}
}