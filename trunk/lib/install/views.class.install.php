<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tviewsInstall($self) {
$self->lock();
$default = $self->add('default');
$home = $self->add('home');
$home->ajax = false;
$self->unlock();
}

?>