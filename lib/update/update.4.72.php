<?php

function update472() {
litepublisher::$urlmap->delete('/comusers.htm');
  litepublisher::$urlmap->addget('/comusers.htm', 'tcomusers');
litepublisher::$classes->items['tuitabs'] = array('htmlresource.class.php', '');


if (litepublisher::$classes->exists('tpolls')) {
$polls = tpolls::instance();
$polls->data['deftitle'] = $polls->data['title'];
unset($polls->data['title']);
$polls->data['deftype'] = 'radio';
$polls->data['defadd'] = false;
$about = tplugins::getabout('polls');
$polls->data['defitems'] = $about['items'];
$polls->save();
}

}