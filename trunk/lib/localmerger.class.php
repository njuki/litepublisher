<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tlocalmerger extends titems {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = false;
    parent::create();
    $this->basename = 'localmerger';
  }
  
  public function save() {
    if ($this->lockcount > 0) return;
    parent::save();
    $this->parse();
  }
  
  public function normfilename($filename) {
    $filename = trim($filename);
    if (strbegin($filename,litepublisher::$paths->home)) $filename = substr($filename, strlen(litepublisher::$paths->home));
    if (empty($filename)) return false;
    $filename = str_replace(DIRECTORY_SEPARATOR, '/', $filename);
    $filename = '/' . ltrim($filename, '/');
    return $filename;
  }
  
  public function add($name, $filename) {
    if (!($filename = $this->normfilename($filename))) return false;
    if (!isset($this->items[$name])) {
      $this->items[$name] = array(
      'files' => array($filename),
      'texts' => array()
      );
    } else {
      if (in_array($filename, $this->items[$name]['files'])) return false;
      $this->items[$name]['files'][] = $filename;
    }
    $this->save();
    return count($this->items[$name]['files']) - 1;
  }
  
  public function deletefile($name, $filename) {
    if (!isset($this->items[$name])) return false;
    if (!($filename = $this->normfilename($filename))) return false;
    if (false === ($i = array_search($filename, $this->items[$name]['files']))) return false;
    array_delete($this->items[$name]['files'], $i);
    $this->save();
  }
  
  public function setfromstring($name, $s) {
    $this->lock();
    if (isset($this->items[$name])) {
      $this->items[$name]['files'] = array();
    } else {
      $this->items[$name] = array(
      'files' => array(),
      'texts' => array()
      );
    }
    
    $a = explode("\n", trim($s));
    foreach ($a as $filename) {
      $this->add($name, trim($filename));
    }
    $this->unlock();
  }

  public function addtext($name, $section, $s) {
$s = trim($s);
if ($s != '') $this->addsection($name, $section, tini2array::parsesection($s));
}
  
  public function addsection($name, $section, array $items) {
    if (!isset($this->items[$name])) {
      $this->items[$name] = array(
      'files' => array(),
      'texts' => array($key => $items)
      );
    } elseif (!isset(      $this->items[$name]['texts'][$section])) {
      $this->items[$name]['texts'][$section] = $items;
} else {
      $this->items[$name]['texts'][$section] = $items + $this->items[$name]['texts'][$section];
    }
    $this->save();
    return count($this->items[$name]['texts']) - 1;
  }
  
  public function deletetext($name, $key) {
    if (!isset($this->items[$name]['texts'][$key])) return;
    unset($this->items[$name]['texts'][$key]);
    $this->save();
    return true;
  }

public function getrealfilename($filename) {
$name = substr($filename, 0, strpos($filename, '/'));
$dir = isset(litepublisher::$_paths[$name]) ? litepublisher::$_paths[$name] ? litepublisher::$paths->home;
return $dir . str_replace('/', DIRECTORY_SEPARATOR, $filename);
}
  
  public function parse() {
$savedir = litepublisher::$paths->data . 'languages' . DIRECTORY_SEPARATOR;
    foreach ($this->items as $name => $items) {
      $ini = array();
      foreach ($items['files'] as $filename) {
        $realfilename = $this->getrealfilename($filename);
        if  (!file_exists($realfilename)) $this->error(sprintf('The file "%s" not found', $filename));
if (!($parsed = parse_ini_file($realfilename, true))) $this->error(sprintf('Error parse "%s" ini file', $realfilename));
if (count($ini) == 0) {
$ini = $parsed;
} else {
foreach ($parsed as $section => $itemsini) {
$ini[$section] = isset($ini[$section]) ? $itemsini + $ini[$section] : $itemsini;
}
}

foreach ($items['texts'] as $section = $itemsini) {
$ini[$section] = isset($ini[$section]) ? $itemsini + $ini[$section] : $itemsini;
}

tfilestorage::savevar($savedir . $name , $ini);
    }
}

} //class