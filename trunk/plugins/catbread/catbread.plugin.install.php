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

$about = tplugins::getabout(basename(dirname(__file__)));
//bootstrap breadcrumb component
$self->tml = array(
'container' => '<div id="breadcrumb-container">%s</div>',
'items' => '<div id="breadcrumb-items">
<ol class="breadcrumb">
$item
</ol></div>',
'item' => '<li><a href="$link">$title</a></li>',
'active' => '<li class="active">$title</li>',
'child' => '<li><button id="breadcrumbs-toggle" class="btn btn-default" data-target="#breadcrumbs-childs" data-toggle="dropdown"><span class="caret"></span> <span class="sr-only">' . $about['showchilds'] . '</span></button></li>',
'childitems' => '<div id="breadcrumbs-childs" class="dropdown">
<ul class="dropdown-menu" role="menu">
$item
</ul></div>',
      'childitem' =>'<li><a href="$link" title="$title">$title</a>$subitems</li>',
      'childsubitems' =>       '<ul>$item</ul>',

'sameitem' => '<li><a href="$link">$title</a></li>',
'sameitems' => '<div id="breadcrumbs-same">' . $about['seealso'] .
'<ul class="list-inline">$item</ul></div>',
);

$self->save();
}

function catbreaduninstall($self) {
$self->cats->unbind($self);
tposts::i()->unsub($self);
}