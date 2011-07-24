<?php

function update472() {
litepublisher::$urlmap->delete('/comusers.htm');
  litepublisher::$urlmap->addget('/comusers.htm', 'tcomusers');

if (litepublisher::$classes->exists('tpolls')) {
$polls = tpolls::instance();
$polls->data['deftitle'] = $polls->data['title'];
unset($polls->data['title']);
$polls->data['deftype'] = 'radio';
$polls->data['defitems'] = 'Yes,No';
$polls->data['defadd'] = false;
$polls->save();
}
}