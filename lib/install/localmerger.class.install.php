<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tlocalmergerInstall($self) {
  $dir =litepublisher::$paths->data . 'languages';
  if (!is_dir($dir)) @mkdir($dir, 0777);
  @chmod($dir, 0777);
  
  $lang = litepublisher::$options->language;
  $self->lock();
  $self->add('default', "lib/languages/$lang/default.ini");
  $self->add('admin', "lib/languages/$lang/admin.ini");
  $self->add('theme', "lib/languages/$lang/theme.ini");
if (litepublisher::$options->language != 'en') {
$self->add('translit', "lib/languages/$lang/translit.ini");
} else {
$self->items['translit'] = array('files => array(), 'text' => array());
}
  $self->add('install', "lib/languages/$lang/install.ini");
  
  $self->addhtml('lib/languages/adminhtml.ini');
  
  $self->unlock();


//post install
  litepublisher::$options->timezone = tlocal::get('installation', 'timezone');
    date_default_timezone_set(tlocal::get('installation', 'timezone'));

$html = tadminhtml::instance();
$html->loadinstall();
}