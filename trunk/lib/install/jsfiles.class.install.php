<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tjsfilesInstall($self) {
if ('tjsfile' == get_class($self)) {
$self->lock();
$self->add(sprintf('/js/jquery/jquery.%s.js', litepublisher::$site->jquery_version));
$self->add('/js/prettyphoto/js/jquery.prettyPhoto.js');
$self->add('/js/litepublisher/cookie.min.js');
$self->add('/js/litepublisher/litepublisher.utils.min.js');
$self->add('/js/litepublisher/widgets.min.js');
$self->add('/js/litepublisher/players.min.js');
$self->unlock();
}

$template = ttemplate::instance();
$template->heads .= $template->getjavascript('$site.url$template.' . $self->basename);
$template->save();
}

function tadminjsfilesInstall($self) {
$self->lock();
$self->add(sprintf('/js/jquery/ui-%1$s/jquery-ui-%1$s.custom.min.js', litepublisher::$site->jqueryui_version);
$self->add('/js/litepublisher/filebrowser.min.js');
$self->add('/js/litepublisher/admin.min.js');
$self->unlock();
}

