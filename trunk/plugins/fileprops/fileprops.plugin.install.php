<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tfilepropspluginInstall($self) {
  litepublisher::$urlmap->addget('/admin/fileprops.htm', get_class($self));
  
  $js = file_get_contents(dirname(__file__) . DIRECTORY_SEPARATOR . 'fileprops.min.js');
  $about = tplugins::getabout(tplugins::getname(__file__));
  $js = str_replace('%%lang_titledialog%%', $about['titledialog'], $js);
  $lang = tlocal::instance('common');
  $theme = ttheme::instance();
  $js = $theme->replacelang($js, $lang);
  file_put_contents(dirname(__file__) . DIRECTORY_SEPARATOR . 'min.js', $js);
  
  $jsmerger = tjsmerger::instance();
  $jsmerger->addtext('admin', 'fileprops', $js);
}

function tfilepropspluginUninstall($self) {
  turlmap::unsub($self);
  
  $jsmerger = tjsmerger::instance();
  $jsmerger->deletetext('admin', 'fileprops');
}