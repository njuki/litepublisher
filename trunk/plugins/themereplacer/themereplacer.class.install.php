<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function titemsreplacerInstall($self) {
$dir = basename(dirname(__file__));
litepublisher::$classes->add('tviewthemereplacer', 'themereplacer.class.php', $dirname);
litepublisher::$classes->add('tthemereplacer', 'themereplacer.class.php', $dirname);

tviews::i()->deleted = $self->delete;
  ttheme::clearcache();
}

function titemsreplacerUninstall($self) {
tviews::i()->unbind($self);
litepublisher::$classes->delete('tviewthemereplacer');
litepublisher::$classes->delete('tthemereplacer');

  ttheme::clearcache();
}