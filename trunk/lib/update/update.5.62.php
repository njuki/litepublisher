<?php

function update562() {
$groups = tusergroups::i();
$groups->data['defaulthome'] = '/admin/';
$groups->save();
}