<?php

function update551() {
$js = tjsmerger::i();
$js->lock();
  $js->add('default', '/js/litepublisher/filelist.min.js');
  $js->add('default', '/js/litepublisher/youtubefix.min.js');
$js->unlock();

litepublisher::$classes->items['tsinglemenu'] = array('menu.class.php', '');
litepublisher::$classes->save();
}