<?php
/**
* lite publisher
* copyright (c) 2010 - 2013 vladimir yushko http://litepublisher.ru/ http://litepublisher.com/
* dual licensed under the mit (mit.txt)
* and gpl (gpl.txt) licenses.
**/

function catbreadinstall($self) {
$self->cats->onbeforecontent = $self->beforecat;
tposts::i()->beforecontent = $self->beforepost;

//bootstrap breadcrumb component
$self->tml = array(
'items' => '<ol class="breadcrumb">$item</ol>',
'item' => '<li><a href="$link">$title</a></li>',
'active' => '<li class="active">$title</li>',
);

$self->save();
}

function catbreaduninstall($self) {
$self->cats->unbind($self);
tposts::i()->unsub($self);
}