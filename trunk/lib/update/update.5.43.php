<?php

function update543() {
  litepublisher::$site->jquery_version = '1.8.2';
  litepublisher::$site->save();

    $dir = litepublisher::$paths->lib . 'install/';
    $manager = tdbmanager ::i();
    $manager->createtable('imghashes', file_get_contents($dir .'imghashes.sql'));

$p = tmediaparser::i();
    $p->data['maxwidth'] = 0;
    $p->data['maxheight'] = 0;
$p->save();
}