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
  
  $template = ttemplate::instance();
  $template->ongetmenu = $self->ongetmenu;
}

function tcategoriesmenuUninstall($self) {
  $template = ttemplate::instance();
  $template->unsubscribeclass($this);
  
  $categories = tcategories::instance();
  $categories->unsubscribeclass($this);
}