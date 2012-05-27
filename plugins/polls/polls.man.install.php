<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tpollsmanInstall($self) {
tcron::i()->addweekly(get_class($self), 'optimize', null);
}

function tpollsmanUninstall($self) {
tcron::i()->deleteclass(get_class($self));
}