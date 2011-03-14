<?php

function update436() {
$items = &litepublisher::$classes->items;
  $ini = parse_ini_file(litepublisher::$paths->lib.'install' . DIRECTORY_SEPARATOR . 'classes.ini', true);
  $replace = dbversion ? '.class.db.' : '.class.files.';
 $exclude = !dbversion ? '.class.db.' : '.class.files.';

foreach ($ini['debug'] as $class => $filename) {
if (!isset($items[$class])) continue;
    if (strpos($filename, $exclude)) continue;
    if (!file_exists(litepublisher::$paths->lib . $filename)){
      $filename = str_replace('.class.', $replace, $filename);
      if (!file_exists(litepublisher::$paths->lib . $filename))continue;
    }


litepublisher::$classes->items[$class][2] = $filename;

$filename = $ini[$class];
    if (!file_exists(litepublisher::$paths->lib . $filename)){
      $filename = str_replace('.class.', $replace, $filename);
      if (!file_exists(litepublisher::$paths->lib . $filename))continue;
    }

litepublisher::$classes->items[$class][0] = $filename;
}
litepublisher::$classes->save();
}
