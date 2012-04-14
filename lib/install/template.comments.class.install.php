<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function ttemplatecommentsInstall($self) {
tlocal::usefile('install');
$lang = tlocal::i('beforecommentsform');
$self->data['reg'] = $lang->reg;
$self->data['noreg'] = $lang->noreg;
$self->data['guest'] = $lang->guest;
$self->data['comuser'] = $lang->comuser;
$self->save();
}