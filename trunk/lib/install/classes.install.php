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
  $replace = dbversion ? '.class.db.' : '.class.files.';
  $exclude = !dbversion ? '.class.db.' : '.class.files.';
  
  $ini = parse_ini_file($paths['lib'].'install' . DIRECTORY_SEPARATOR . 'classes.ini', true);
  foreach ($ini['items'] as $class => $filename) {
    //исключить из списка только файлы для бд или файлов
    if (strpos($filename, $exclude)) continue;
    if (!file_exists($paths['lib'] . $filename)){
      $filename = str_replace('.class.', $replace, $filename);
      if (!file_exists($paths['lib'] . $filename))continue;
    }
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
  $urlmap = turlmap::instance();
  $urlmap->lock();
  $posts = tposts::instance();
  $posts->lock();
  
  $xmlrpc = TXMLRPC::instance();
  $xmlrpc->lock();
  //tdata::$GlobalLock = true;
  foreach( $classes->items as $class => $item) {
    //echo "$class\n";
    if (preg_match('/^titemspostsowner|tcomment$/', $class)) continue;
    $obj = getinstance($class);
    if (method_exists($obj, 'install')) $obj->install();
  }
  
  $xmlrpc->unlock();
  $posts->unlock();
  $urlmap->unlock();
}

?>