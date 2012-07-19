<?php

function update538() {
$lang = tlocal::admin('editor');
$js = tjsmerger::i();
$js->lock();
  $section = 'default';
  //$js->add($section, '/js/plugins/class-extend.min.js');
$s = implode("\n", $js->items[$section]['files']);
$p = 'jquery.prettyPhoto.js';
$s = str_replace($p, $p . "\n/js/plugins/class-extend.min.js", $s);
$js->setfiles($section, $s);
$js->unlock();
}