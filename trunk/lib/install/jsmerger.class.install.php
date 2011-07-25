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

if ('tjsmerger' == get_class($self)) {
$self->lock();
$self->add('/js/jquery/jquery-$site.jquery_version.min.js');
$self->add('/js/prettyphoto/js/jquery.prettyPhoto.js');
$self->add('/js/litepublisher/cookie.min.js');
$self->add('/js/litepublisher/litepublisher.utils.min.js');
$self->add('/js/litepublisher/widgets.min.js');
$self->add('/js/litepublisher/players.min.js');
$self->unlock();
}

$template = ttemplate::instance();
$template->heads .= $template->getjavascript('$site.files$template.' . $self->basename);
$template->save();

$updater = tupdater::instance();
$updater->onupdated = $self->save;
}

function tjsmergerUninstall($self) {
$updater = tupdater::instance();
$updater->unsubscribeclass($self);
}

function tadminjsmergerInstall($self) {
$self->lock();
$self->add('/js/jquery/ui-$site.jqueryui_version/jquery-ui-$site.jqueryui_version.custom.min.js');
$self->add('/js/litepublisher/filebrowser.min.js');
$self->add('/js/litepublisher/admin.min.js');

$self->addtext('pretty',
'$(document).ready(function() {
    $("a[rel^=\'prettyPhoto\']").prettyPhoto();
  });');

$self->unlock();
}

