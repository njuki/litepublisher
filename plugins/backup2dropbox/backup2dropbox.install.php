<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tbackup2dropboxInstall($self) {
  $cron = tcron::i();
  $self->idcron = $cron->add('week', get_class($self), 'send', null);
  $self->save();
}

function tbackup2dropboxUninstall($self) {
tcron::i()->delete($self->idcron);
}