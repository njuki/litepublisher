<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tmetatagsInstall($self) {
litepublisher::$classes->classes['metatags'] = get_class($self);
litepublisher::$classes->save();

  tthemeparser::i()->parsed = $self->themeparsed;
  ttheme::clearcache();
}

function tmetatagsUninstall($self) {
  tthemeparser::i()->unbind($self);
  ttheme::clearcache();

unset(litepublisher::$classes->classes['metatags']);
litepublisher::$classes->save();
}