<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tblackipInstall($self) {
  tspamfilter::i()->onstatus = $self->filter;
}

function tblackipUninstall($self) {
  tspamfilter::i()->unbind($self);
}