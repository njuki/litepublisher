<?php

function update562() {
$m = tmenus::i();
$m->data['showsubmenu'] = false;
$m->save();

$m = tadminmenus::i();
$m->data['showsubmenu'] = false;
$m->save();

$groups = tusergroups::i();
$groups->data['defaulthome'] = '/admin/';
$groups->save();
}