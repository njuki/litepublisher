<?php

function update551() {
litepublisher::$classes->add('tsinglemenu', 'menu.class.php');

$js = tjsmerger::i();
$js->lock();
  $js->add('default', '/js/litepublisher/filelist.min.js');
  $js->add('default', '/js/litepublisher/youtubefix.min.js');
$js->unlock();
}