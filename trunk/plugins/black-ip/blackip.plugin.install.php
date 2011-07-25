<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tblackipInstall($self) {
  $spamfilter = tspamfilter::instance();
  $spamfilter->onstatus = $self->filter;
}

function tblackipUninstall(&$self) {
  $spamfilter = tspamfilter::instance();
  $spamfilter->unsubscribeclass($self);
}