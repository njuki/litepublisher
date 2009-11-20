<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class tplugins extends TItems {

  public static function instance() {
    return getinstance(__class__);
  }
  
    protected function create() {
    parent::create();
    $this->basename = 'plugins' . DIRECTORY_SEPARATOR  . 'index';
  }
  
  public function getabout($name) {
    global $paths, $options;
    $about = parse_ini_file($paths['plugins'] .  $name . DIRECTORY_SEPARATOR . 'about.ini', true);
if (isset($about[$options->language])) {
$about['about'] = $about[$options->language] + $about['about'];
}

return $about['about'];
  }
  
  public function add($name) {
    global $paths;
    if (!@is_dir($paths['plugins'] . $name)) return false;
    $about = $this->GetAbout($name);
    return $this->AddExt($name, $about['classname'], $about['filename']);
  }
  
  public function AddExt($name, $classname, $filename) {
    global $classes;
    $this->items[$name] = array(
    'id' => ++$this->autoid,
    'class' => $classname,
    'file' => $filename
    );
    $this->Save();
    $classes->Add($classname, $filename, $name);
    $this->Added($name);return $this->autoid;
  }
  
  public function delete($name) {
    global $classes, $paths;
    if (!isset($this->items[$name])) return false;
    $item = $this->items[$name];
    unset($this->items[$name]);
    $this->save();
    if (@class_exists($item['class'])) {
      $plugin = getinstance($item['class']);
      if (is_a($plugin, 'tplugin')) {
        @unlink($paths['data']. $plugin->getbasename() . '.php');
        @unlink($paths['data']. $plugin->getbasename() . 'bak..php');
      }
    }
    $classes->delete($item['class']);
    $this->deleted($name);
  }
  
  public function deleteclass($class) {
    foreach ($this->items as $name => $item) {
      if ($item['class'] == $class) $this->Delete($name);
    }
  }
  
  public function getplugins() {
    return array_keys($this->items);
  }
  
  public function update($list) {
    global $paths;
    $add = array_diff($list, array_keys($this->items));
    $delete  = array_diff(array_keys($this->items), $list);
    $delete  = array_intersect($delete, tfiler::getdir($paths['plugins']));
    $this->lock();
    foreach ($delete as $name) {
$this->Delete($name);
    }
    
    foreach ($add as $name) {
$this->Add($name);
    }
    
    $this->unlock();
  }
  
  public function setplugins($list) {
    $names = array_diff($list, array_keys($this->items));
    foreach ($names as $name) {
        $this->Add($name);
    }
  }
  
  public function deleteplugins($list) {
$names = array_intersect(array_keys($this->items), $list);
    foreach ($names as $name) {
        $this->Delete($name);
    }
  }
  
  public function upload($name, $files) {
    global $paths;
    if (!@file_exists($paths['plugins'] . $name)) {
      if (!@mkdir($paths['plugins'] . $name, 0777)) return $this->Error("Cant create $name folder in plugins");
      @chmod($paths['plugins'] . $name, 0777);
    }
    $dir = $paths['plugins'] . $name . DIRECTORY_SEPARATOR  ;
    foreach ($files as $filename => $content) {
      file_put_contents($dir . $filename, base64_decode($content));
    }
  }
  
} //class

?>