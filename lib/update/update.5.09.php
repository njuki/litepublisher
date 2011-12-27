<?php

function update509() {
litepublisher::$site->video_width =420;
litepublisher::$site->video_height = 300;

if (litepublisher::$classes->exists('tembeddedplayers')) {
tembeddedplayers::i()->install();
}
}