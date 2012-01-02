<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tblackipInstall($self) {
  $spamfilter = tspamfilter::i();
  $spamfilter->onstatus = $self->filter;
}

function tblackipUninstall(&$self) {
  $spamfilter = tspamfilter::i();
  $spamfilter->unbind($self);
}