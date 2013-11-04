<?php

function update573() {
$js = tjsmerger::i();
$js->lock();
$section = 'default';
  $js->deletefile($section, '/js/litepublisher/prettyphoto.dialog.min.js');
   $js->add($section, '/js/litepublisher/dialog.min.js');
    $js->add($section, '/js/litepublisher/dialog.pretty.min.js');
      $js->add($section, '/js/litepublisher/dialog.bootstrap.min.js');
 
$js->unlock();

$css = tcssmerger::i();
$css->lock();
  $css->add('default', '/js/litepublisher/css/button.min.css');
$css->deletefile('default', '/plugins/shop/paymethods/resource/button.min.css');    
$css->unlock();
}