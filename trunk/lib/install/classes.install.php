<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

function installclasses($language) {
ParseClassesIni();
$options = toptions::instance();
$options->lock();
        require_once(dirname(__file__) . DIRECTORY_SEPARATOR. 'options.class.install.php');
    $password = installoptions($language);
doinstallclasses();
$options->unlock();
    return $password;
}

function ParseClassesIni() {
  global $classes, $paths, $ini;
  $ini = parse_ini_file($paths['lib'].'install' . DIRECTORY_SEPARATOR . 'classes.ini', true);
$section = dbversion ? 'db' : 'files';
  foreach ($ini[$section] as $class => $filename) {
    $classes->items[$class] = array($filename, '');
  }

  foreach ($ini['items'] as $class => $filename) {
    $classes->items[$class] = array($filename, '');
  }

  $classes->classes = $ini['classes'];
$classes->interfaces = $ini['interfaces'];
  $classes->Save();

//так как ttheme при первом же обращении парсит тему
@mkdir($paths['data'] . 'themes', 0777);
@chmod($paths['data'] . 'themes', 0777);
}

function doinstallclasses() {
    global  $classes, $options, $urlmap, $posts;
    $options->lock();
    $urlmap = turlmap::instance();
    $urlmap->lock();
    $posts = tposts::instance();
    $posts->lock();
//tdata::$GlobalLock = true;
    foreach( $classes->items as $class => $item) {
//echo "$class\n";
      $obj = getinstance($class);
      if (method_exists($obj, 'install')) $obj->install();
    }
    $posts->unlock();
    $urlmap->unlock();
    $options->unlock();
}

?>