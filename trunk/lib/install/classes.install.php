<?php

function TClassesInstall(&$self) {
  global $paths, $ini;
  $ini = parse_ini_file($paths['lib'].'install' . DIRECTORY_SEPARATOR . 'classes.ini', true);
  foreach ($ini['items'] as $class => $filename) {
    $self->items[$class] = array($filename, '');
  }
  $self->classes = $ini['classes'];
$self->interfaces = $ini['interfaces'];
  $self->Save();
}

?>