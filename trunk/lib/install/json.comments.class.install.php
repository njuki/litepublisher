<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function TjsonCommentsInstall($self) {
$json = tjsonserver::i();
$json->lock();
$json->addevent('comment_delete', get_class($self), 'comment_delete');
$json->unlock();
}

function TjsonCommentsUninstall($self) {
tjsonserver::i()->unbind($self);
}