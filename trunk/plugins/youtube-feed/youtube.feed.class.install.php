<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tyoutubefeedInstall($self) {
  $about = tplugins::getabout(tplugins::getname(__file__));
  $admin = tadminmenus::i();
  $idfiles = $admin->url2id('/admin/files/');
  $admin->createitem($idfiles, 'youtube', 'author', 'tadminfiles');
  
tthemeparser::i()->parsed = $self->themeparsed;
  ttheme::clearcache();
  
    $man = tdbmanager::i();
    $man->alter('files', "modify `media` enum('bin','image','icon','audio','video','document','executable','text','archive', 'youtube') default 'bin'");
}

function tyoutubefeedUninstall($self) {
  $admin = tadminmenus::i();
  $admin->deleteurl('/admin/files/youtube/');
  
tthemeparser::i()->unbind($self);
}