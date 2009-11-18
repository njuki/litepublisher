<?php

function ParseClassesIni(tclasses $self) {
  global $paths, $ini;
  $ini = parse_ini_file($paths['lib'].'install' . DIRECTORY_SEPARATOR . 'classes.ini', true);
$section = dbversion ? db' : 'files';
  foreach ($ini[$section] as $class => $filename) {
    $self->items[$class] = array($filename, '');
  }

  foreach ($ini['items'] as $class => $filename) {
    $self->items[$class] = array($filename, '');
  }

  $self->classes = $ini['classes'];
$self->interfaces = $ini['interfaces'];
  $self->Save();
}

function tclassesInstall() {
    global  $classes, $options, $urlmap, $posts;
    $options->lock();
    $urlmap = turlmap::instance();
    $Urlmap->Lock();
    $posts = tposts::instance();
    $posts->lock();
    foreach( $classes->items as $class => $item) {
      $obj = getinstance($class);
      if (method_exists($obj, 'install')) $Obj->install();
    }
    $posts->unlock();
    $urlmap->unlock();
    $options->unlock();
}

?>