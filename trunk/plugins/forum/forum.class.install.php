<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tforumInstall($self) {
  litepublisher::$options->reguser = true;
tadminoptions::i()->usersenabled = true;

$lang = tlocal::admin('forum);
$self->cat = tcategories::i()->add(0, $lang->forum);
$self->save();
}

function tforumUninstall($self) {
}