<?php

function update501() {
$r = trobotstxt::i();
$r->lock();
  $r->AddDisallow('/wlwmanifest.xml');
  $r->AddDisallow('/rsd.xml');
  $r->unlock();
}
