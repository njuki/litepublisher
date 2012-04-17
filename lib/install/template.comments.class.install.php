<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function ttemplatecommentsInstall($self) {
tlocal::usefile('install');
$lang = tlocal::i('beforecommentsform');
foreach (array('logged', 
as $name) {
$self->data[$name] = sprintf('<p>%s</p>', $lang->$name);
}

$self->data['idgroups'] = tusergroups::i()->cleangroups('author', 'commentator');
$self->save();
}