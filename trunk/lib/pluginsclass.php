<?php

class TPlugins extends TItems {
 
 protected function CreateData() {
  parent::CreateData();
  $this->basename = 'plugins' . DIRECTORY_SEPARATOR  . 'index';
 }
 
 public static function &Instance() {
  return GetInstance(__class__);
 }
 
 public function GetAbout($name) {
  global $paths;
  $filename = $paths['plugins'] .  $name . DIRECTORY_SEPARATOR . 'about.ini';
  return parse_ini_file($filename);
 }
 
 public function Add($name) {
  global $paths;
  if (!@is_dir($paths['plugins'] . $name)) return false;
  $about = $this->GetAbout($name);
  return $this->AddExt($name, $about['classname'], $about['filename']);
 }
 
 public function AddExt($name, $classname, $filename) {
  $this->items[$name] = array(
  'id' => ++$this->lastid,
  'class' => $classname,
  'file' => $filename
  );
  $this->Save();
  TClasses::Register($classname, $filename, $name);
  $this->Added($name);return $this->lastid;
 }
 
 public function Delete($name) {
  global $paths;
  if (!isset($this->items[$name])) return false;
  $item = $this->items[$name];
  unset($this->items[$name]);
  $this->Save();
  TClasses::Unregister($item['class']);
  $plugin = &GetInstance($item['class']);
  @unlink($paths['data']. $plugin->GetBaseName() . '.php');
  $this->Deleted($name);
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