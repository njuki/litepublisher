<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tcategoriesmenuInstall($self) {
  $categories = tcategories::instance();
  $categories->changed = $self->buildtree;
  $self->buildtree();

tadminviews::replacemenu('tmenus', get_class($self));
}

function tcategoriesmenuUninstall($self) {
tadminviews::replacemenu(get_class($self), 'tmenus');

  $categories = tcategories::instance();
  $categories->unsubscribeclass($self);
}