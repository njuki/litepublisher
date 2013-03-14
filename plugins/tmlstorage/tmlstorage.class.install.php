<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tmlstorageInstall($self) {
litepublisher::$classes->deleted = $self->classdeleted;
}

function tmlstorageUninstall($self) {
litepublisher::$classes->unbind($self);
}