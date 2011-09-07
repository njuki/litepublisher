<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tfoafutilInstall($self) {
  $cron = tcron::instance();
  $cron->add('day', get_class($self), 'CheckFriendship', null);
}

function tfoafutilUninstall($self) {
  $cron = tcron::instance();
  $cron->deleteclass(get_class($self));
}
