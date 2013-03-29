<?php

function update553() {
tcssmerger::i()->save();

$js = tjsmerger::i();
$js->lock();
$section = 'posteditor';
  $js->add($section, '/js/plugins/filereader.min.js');
  $js->add($section, '/js/litepublisher/uploader.html.min.js');
  $js->add($section, '/js/litepublisher/uploader.flash.min.js');
$js->unlock();
}