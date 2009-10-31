<?php

function tusergroupsInstall($self) {
$self->lock();
$self->add('admin');
$self->add('editor');
$self->add('author');
$self->add('moderator');
$self->add('subscriber');
$self->add('nobody');
$self->unlock();
}

?>