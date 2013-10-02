<?php

function update572() {
$css = tcssmerger::i();
$css->lock();
  $css->add('default', '/js/litepublisher/css/button.min.css');
$css->deletefile('default', '/plugins/shop/paymethods/resource/button.min.css');    
$css->unlock();
}