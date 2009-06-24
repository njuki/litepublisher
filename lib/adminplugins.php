<?php

class TAdminPlugins extends TAdminPage {
 private $submenu;
 private $adminplugins;
 private $plugin;
 
 public static function &Instance() {
  return GetInstance(__class__);
 }
 
 protected function CreateData() {
  parent::CreateData();
  $this->basename = 'plugins';
  $this->adminplugins = array();
 }
 
 public function Getcontent() {
  switch ($this->arg) {
   case null:
   return $this->GetPluginsMenu(true);
   
   default:
   $result = $this->GetPluginsMenu(false);
   $result .= $this->GetPluginContent($this->arg, 'Getcontent');
   return $result;
  }
  
 }
 
 private function GetPluginsMenu($radio) {
  global $Options, $paths;
  if (!empty($this->submenu)) return $this->submenu;
  $this->submenu ='';
  $result = '';
  $html = &THtmlResource::Instance();
  $html->section = $this->basename;
  $lang = &TLocal::Instance();
  
  $list = TFiler::GetDirList($paths['plugins']);
  sort($list);
  $plugins = &TPlugins::Instance();
  
  if ($radio) {
   $result = $html->checkallscript;
   eval('$result .= "'. $html->formhead . '\n";');
   $item = $html->item;
  }
  
  foreach ($list as $name) {
   $ini = parse_ini_file($paths['plugins'] . $name . DIRECTORY_SEPARATOR . 'about.ini', true);
   $about = $ini['about'];
   $langini = $paths['plugins'] . $name . DIRECTORY_SEPARATOR . $Options->language . '.ini';
   if (@file_exists($langini) && ($ini = @parse_ini_file($langini, true))) {
    $about= $ini['about'] + $about;
   }
   $checked =  '';
   if (isset($plugins->items[$name])) {
    $checked =  "checked='checked'";
    if (!empty($about['adminclassname'])) {
     $this->adminplugins[$name] = $about;
     eval('$this->submenu .= "'. $html->menuitem . '\n";');
    }
   }
   if ($radio) eval('$result .= "'. $item . '\n";');
  }
  
  if ($radio) {
   eval('$result .= "'. $html->formfooter . '\n";');;
   $result = $this->FixCheckall($result);
  }
  if ($this->submenu != '') $this->submenu = '<p>' . $this->submenu . "</p>\n";
  $this->submenu = str_replace("'", '"', $this->submenu);
  $this->submenu .= $result;
  return $this->submenu;
 }
 
 private function GetPluginContent($name, $method) {
  global $paths;
  $plugins = &TPlugins::Instance();
  $html = &THtmlResource::Instance();
  $html->section = $this->basename;
  $lang = &TLocal::Instance();
  
  if (!isset($plugins->items[$name])) return $this->notfound;
  if (!isset($this->plugin)) {
   $ini = parse_ini_file($paths['plugins'] . $name . DIRECTORY_SEPARATOR . 'about.ini', true);
   $about = $ini['about'];
   if (empty($about['adminclassname'])) return $this->notfound;
   $class = $about['adminclassname'];
   if (!@class_exists($class)) {
    require_once($paths['plugins'] . $name . DIRECTORY_SEPARATOR . $about['adminfilename']);
   }
   $this->plugin = &new $class ();
  }
  
  return $this->plugin->$method();
 }
 
 public function ProcessForm() {
  global $Options, $Urlmap;
  switch ($this->arg) {
   case null:
   $list = array_keys($_POST);
   array_pop($list);
   $plugins = &TPlugins::Instance();
   $plugins->UpdatePlugins($list);
   $html = &THtmlResource::Instance();
   $html->section = $this->basename;
   $lang = &TLocal::Instance();
   eval('$result = "'. $html->updated . '\n";');
   break;
   
   default:
   $result = $this->GetPluginContent($this->arg, 'ProcessForm');
   break;
  }
  
  $Urlmap->ClearCache();
  return $result;
 }
 
}//class
?>