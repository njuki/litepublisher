<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

function tsapepluginInstall($self) {
$self->data['optcode'] = md5unique();
$self->save();

$template = ttemplate::instance();
$template->lock();
$template->onsitebar= $this->onsitebar;
$template->onwidgetcontent = $self->onwidgetcontent;
$template->unlock();

$widgets = twidgets::instance();
$widgets->addext(get_class($self), 'echo', 'links', tlocal::$data['default']['links'], 0, -1);

$urlmap = turlmap::instance();
$urlmap->clearcache();
}
 
function tsapepluginUninstall($self) {
$widgets = twidgets::instance();
$widgets->deleteclass($self);

$template = ttemplate::instance();
$template->unsubscribeclass($self);

$urlmap = turlmap::instance();
$urlmap->clearcache();
}

?>