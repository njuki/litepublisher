<?php

function TClassesInstall(&$self) {
  global $paths;
  $ini = parse_ini_file($paths['libinclude'] . 'classes.ini', true);
  foreach ($ini['items'] as $class => $filename) {
    $self->items[$class] = array($filename, '');
  }
  $self->classes = $ini['classes'];
  $self->Save();
}
?>