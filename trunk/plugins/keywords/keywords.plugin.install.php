<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

function tkeywordspluginInstall($self) {
  global $paths, $classes;
  @mkdir($paths['data'] . 'keywords', 0777);
  @chmod($paths['data'] . 'keywords', 0777);
  
$widgets = twidgets::instance();
$widgets->addext(get_class($self), 'nocache', '', '', -1, $widgets->count - 1);

$classes->add('tkeywordsevents'
$handler = tkeywordsevents::instance();
  $urlmap = turlmap::instance();
$urlmap->lock();
  $Urlmap->afterrequest = $self->parseref;
$urlmap->deleted = $self->urldeleted;
$urlmap->unlock();
 }
 
function tkeywordspluginUninstall($self) {
  global $paths;
  turlmap::unsub($self);
  //TFiler::DeleteFiles($paths['data'] . 'keywords' . DIRECTORY_SEPARATOR  , true);
 }

?>