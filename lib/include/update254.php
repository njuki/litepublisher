<?php

function Update254() {
$Urlmap = TUrlmap::Instance();
unset($Urlmap->get['/comments/subscribe/']);
  $Urlmap->AddGet('/admin/subscribe/', 'TSubscribe', null);
}
?>