<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function ticonsInstall($self) {
  $files = tfiles::instance();
  $files->lock();
  $files->deleted = $self->filedeleted;
  $mparser = tmediaparser::instance();
  $self->items['post'] = $mparser->addicon('icon/document-list.png');
  $self->items['categories'] = $mparser->addicon('icon/asterisk.png');
  $self->items['tags'] = $mparser->addicon('icon/tag-label.png');
  $self->items['archives'] = $mparser->addicon('icon/book.png');
  
  $self->items['audio'] = $mparser->addicon('icon/document-music.png');
  $self->items['video'] = $mparser->addicon('icon/film.png');
  $self->items['bin'] = $mparser->addicon('icon/document-binary.png');
  $self->items['document'] = $mparser->addicon('icon/document-text.png');
  $self->items['news'] = $mparser->addicon('icon/blog-blue.png');
  $files->unlock();
  
  /*
  $self->items['update'] = $mparser->addicon('icon/arrow-circle-double.png');
  $self->items['develop'] = $mparser->addicon('icon/user-black.png');
  $self->items['idea'] = $mparser->addicon('icon/light-bulb.png');
  $self->items['sql'] = $mparser->addicon('icon/database-network.png');
  $self->items['multiadmin'] = $mparser->addicon('icon/user-silhouette.png');
  */
  $self->save();
}

?>