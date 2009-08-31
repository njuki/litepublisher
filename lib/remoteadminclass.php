<?php

class TRemoteAdmin extends TEventClass {
  
  protected function CreateData() {
    parent::CreateData();
    $this->basename = 'remoteadmin';
  }
  
  public static function &Instance() {
    return GetInstance(__class__);
  }
  
  public function GetClasses() {
    global $classes;
    return $classes->items;
  }
  
  public function ThemeExists($name) {
    global $paths;
    return @is_dir($paths['themes'] . $name);
  }
  
  public function PluginExists($name) {
    global $paths;
    return @is_dir($paths['plugins']. $name);
  }
  
  public function GetThemesList() {
    global $paths;
    return TFiler::GetDirList($paths['themes']);
  }
  
  public function GetPluginsList() {
    global $paths;
    return TFiler::GetDirList($paths['plugins']);
  }
  
  public function SetPlugins($names) {
    $plugins = &TPlugins::Instance();
    $plugins->SetPlugins($names);
  }
  
  public function DeletePlugins($names) {
    $plugins = &TPlugins::Instance();
    $plugins->DeletePlugins($names);
  }
  
  public function SetTheme($name) {
    $template = &TTemplate::Instance();
    $template->themename = $name;
  }
  
  protected function  ReadDirToZip(&$zip, $path, $subdir, $prefix = '') {
    $subdirslashed = str_replace(DIRECTORY_SEPARATOR   , '/', $subdir) . '/';
    $subdirslashed  = ltrim($subdirslashed , '/');
    $hasindex = false;
    if ($fp = @opendir($path . $subdir)) {
      while (FALSE !== ($file = readdir($fp))) {
        if (($file == '.') || ($file == '..')) continue;
        $filename = $path . $subdir .DIRECTORY_SEPARATOR . $file;
        if (@is_dir($filename)) {
          $this->ReadDirToZip($zip, $path, $subdir . DIRECTORY_SEPARATOR   . $file, $prefix);
        } 			else {
          if (preg_match('/(\.bak\.php$)|(\.lok$)/',  $file)) continue;
          $zip->addFile(file_get_contents($filename), "$prefix$subdirslashed$file");
          if (!$hasindex) $hasindex = ($file == 'index.php') || ($file == 'index.htm');
        }
      }
    }
    if (!$hasindex) $zip->addFile('', $prefix . $subdirslashed. 'index.htm');
  }
  
  protected function GetDirAsZip($dir) {
    global $paths;
    require_once($paths['libinclude'] . 'zip.lib.php');
    $zip = new zipfile();
    $this->ReadDirToZip($zip, $dir, '');
    return $zip->file();
  }
  
  protected function RequireZip() {
    global $paths;
    require_once($paths['libinclude'] . 'zip.lib.php');
  }
  
  public function DownloadPlugin($name) {
    global $paths;
    $this->RequireZip();
    $zip = new zipfile();
    $this->ReadDirToZip($zip, $paths['plugins'] . $name, '', "plugins/$name/");
    return $zip->file();
  }
  
  public function DownloadTheme($name) {
    global $paths;
    $this->RequireZip();
    $zip = new zipfile();
    $this->ReadDirToZip($zip, $paths['themes'] . $name, '', "themes/$name/");
    return $zip->file();
  }
  
  public function GetPartialBackup($plugins, $theme, $lib) {
    global $paths;
    $this->RequireZip();
    $zip = new zipfile();
    $this->ReadDirToZip($zip, $paths['data'], '', 'data/');
    if ($lib) {
      $this->ReadDirToZip($zip, $paths['lib'], '', 'lib/');
    }
    if ($theme) {
      $Template = &TTemplate::Instance();
      $themename = $Template->themename;
      $this->ReadDirToZip($zip, $paths['themes'] . $themename, '', "themes/$themename/");
    }
    
    if ($plugins) {
      $plugins = &TPlugins::Instance();
      foreach ($plugins->items as $name => $item) {
        if (@is_dir($paths['plugins'] . $name)) {
          $this->ReadDirToZip($zip, $paths['plugins'] . $name, '', "plugins/$name/");
        }
      }
    }
    
    return $zip->file();
  }
  
  public function Upload(&$content) {
    global $paths;
    $dataprefix = 'data';
    $themesprefix =  'themes/';
    $pluginsprefix = 'plugins/';
    
    require_once($paths['libinclude'] . 'strunzip.lib.php');
    $unzip = new StrSimpleUnzip ();
    $unzip->ReadData($content);
    foreach ($unzip->Entries as  $entry) {
      if ($entry->Error != 0) continue;
      $dir = $entry->Path;
      if ($dataprefix == substr($dir, 0, strlen($dataprefix))) {
        $dir = substr($dir, strlen($dataprefix));
        if (!isset($tmp)) {
          $up = dirname($paths['data']) .DIRECTORY_SEPARATOR;
          $tmp = $up . basename($paths['data']) . '-tmp.tmp' . DIRECTORY_SEPARATOR;
          @mkdir($tmp, 0777);
          @chmod($tmp, 0777);
        }
        $path = $tmp;
      } elseif ($themesprefix == substr($dir, 0, strlen($themesprefix))) {
        $dir = substr($dir, strlen($themesprefix));
        $path = $paths['themes'];
      } elseif ($pluginsprefix == substr($dir, 0, strlen($pluginsprefix))) {
        $dir = substr($dir, strlen($pluginsprefix));
        $path = $paths['plugins'];
      } else {
        //echo $dir, " is unknown dir<br>";
      }
      
      $dir = str_replace('/', DIRECTORY_SEPARATOR  , $dir);
      if (!$this->ForceDirectories($path, $dir)) return $this->Error("cantcreate folder $path$dir");
      $filename = $path . $dir . DIRECTORY_SEPARATOR    . $entry->Name;
      if (false === @file_put_contents($filename, $entry->Data)) {
        return $this->Error("Error saving file $filename");
      }
      @chmod($filename, 0666);
    }
    
    if (isset($tmp)) {
      $old = $up . basename($paths['data']) . '-old-tmp.tmp' . DIRECTORY_SEPARATOR;
      @rename($paths['data'], $old);
      @rename($tmp, $paths['data']);
      TFiler::DeleteFiles($old, true, true);
    }
    
    return true;
  }
  
  public function GetFullBackup() {
    global $paths;
    $this->RequireZip();
    $zip = new zipfile();
    $this->ReadDirToZip($zip, $paths['data'], '', 'data/');
    
    $items = TFiler::GetDirList($paths['plugins']);
    foreach ($items as $name ) {
      $this->ReadDirToZip($zip, $paths['plugins'], $name, "plugins/");
    }
    
    $items = TFiler::GetDirList($paths['themes']);
    foreach ($items as $name ) {
      $this->ReadDirToZip($zip, $paths['themes'] , $name, "themes/");
    }
    
    $this->ReadDirToZip($zip, $paths['lib'], '', 'lib/');
    $this->ReadDirToZip($zip, $paths['files'], '', 'files/');
    
    return $zip->file();
  }
  
  protected function ForceDirectories($path, $dir) {
    if (!@is_dir($path . $dir)) {
      $up = dirname($dir);
      if (($up != '') || ($up != '.'))   $this->ForceDirectories($path, $up);
      if (!@mkdir($path . $dir, 0777)) return $this->Error("cant create $dir folder");
      @chmod($path . $dir, 0777);
    }
    return true;
  }
  
}//class
?>