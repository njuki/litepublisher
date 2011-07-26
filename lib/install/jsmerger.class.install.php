<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tjsmergerInstall($self) {
  $dir = litepublisher::$paths->files . 'js';
  @mkdir($dir, 0777);
  @chmod($dir, 0777);
  
    $self->lock();
$self->items = array();
$section = 'default';
    $self->add($section, '/js/jquery/jquery-$site.jquery_version.min.js');
    $self->add($section, '/js/prettyphoto/js/jquery.prettyPhoto.js');
    $self->add($section, '/js/litepublisher/cookie.min.js');
    $self->add($section, '/js/litepublisher/litepublisher.utils.min.js');
    $self->add($section, '/js/litepublisher/widgets.min.js');
    $self->add($section, '/js/litepublisher/players.min.js');
  $self->addtext($section, 'pretty',
  '$(document).ready(function() {
    $("a[rel^=\'prettyPhoto\']").prettyPhoto();
  });');
  
$section = 'admin';
  $self->add($section, '/js/jquery/ui-$site.jqueryui_version/jquery-ui-$site.jqueryui_version.custom.min.js');
  $self->add($section, '/js/litepublisher/filebrowser.min.js');
  $self->add($section, '/js/litepublisher/admin.min.js');
  
$section = 'comments';
  $self->add($section, '/js/litepublisher/comments.min.js');
  
  $self->unlock();

  $template = ttemplate::instance();
  $template->addtohead($template->getjavascript('$site.files$template.jsmerger_default'));

  $updater = tupdater::instance();
  $updater->onupdated = $self->save;
}

function tjsmergerUninstall($self) {
  $updater = tupdater::instance();
  $updater->unsubscribeclass($self);
}

