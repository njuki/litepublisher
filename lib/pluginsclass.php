<?php

class tplugins extends TItems {
  
  protected function create() {
    parent::create();
    $this->basename = 'plugins' . DIRECTORY_SEPARATOR  . 'index';
  }
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function getabout($name) {
    global $paths;
    $filename = $paths['plugins'] .  $name . DIRECTORY_SEPARATOR . 'about.ini';
    return parse_ini_file($filename);
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
  
  public function GetPlugins() {
    return array_keys($this->items);
  }
  
  public function UpdatePlugins($list) {
    global $paths;
    $dirs = TFiler::GetDirList($paths['plugins']);
    $names = array_keys($this->items);
    $this->Lock();
    
    foreach ($names as $name) {
      if (!in_array($name, $list) && in_array($name, $dirs)) $this->Delete($name);
    }
    
    foreach ($list as $name) {
      if (!in_array($name, $names))  $this->Add($name);
    }
    
    $this->Unlock();
  }
  
  public function SetPlugins($list) {
    $names = array_keys($this->items);
    foreach ($list as $name) {
      if (!in_array($name, $names)) {
        $this->Add($name);
      }
    }
  }
  
  public function DeletePlugins($list) {
    $names = $this->GetPlugins();
    foreach ($list as $name) {
      if (in_array($name, $names)) {
        $this->Delete($name);
      }
    }
  }
  
  public function Upload($name, $files) {
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