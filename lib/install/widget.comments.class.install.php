<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tcommentswidgetInstall($self) {
  litepublisher::$classes->commentmanager->changed = $self->changed;
}

function tcommentswidgetUninstall($self) {
  litepublisher::$classes->commentmanager->unsubscribeclass($self);
}

?>