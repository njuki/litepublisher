<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tabstractcronInstall($self) {
  global $paths, $options;
if (get_class($self) == 'tabstractcron') return;
  $dir = $paths['data'] . 'cron';
  @mkdir($dir, 0777);
  @chmod($dir, 0777);
  
  $self->password =  md5uniq();
  $self->save();
  
  $urlmap  = turlmap::instance();
  $urlmap->add('/croncron.htm', get_class($self), null, 'get');
}

function tabstractcronUninstall($self) {
  turlmap::unsub($self);
}

?>