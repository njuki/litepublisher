<?php

function update509() {
litepublisher::$site->video_width =251;
litepublisher::$site->video_height = 200;

if (litepublisher::$classes->exists('tembeddedplayers')) {
tembeddedplayers::i()->install();
}
}