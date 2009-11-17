<?php

class TAdminService extends TAdminPage {
  public static function &Instance() {
    return GetInstance(__class__);
  }
  
  protected function CreateData() {
    parent::CreateData();
    $this->basename = 'service';
  }
  
  public function Getcontent() {
    global $classes, $Options, $paths;
    $html = &THtmlResource::Instance();
    $html->section = $this->basename;
    $lang = &TLocal::Instance();
    $result = '';
    $checked = "checked='checked'";
    
    switch ($this->arg) {
      case null:
      $result .= $this->HandleUpdate($_GET);
      $posts = &TPosts::Instance();
      $CommentManager = &TCommentManager::Instance();
      $updater = &TUpdater::Instance();
      eval('$result = "' . $html->info . '\n";');
      $islatest= $updater->IsLatest();
      if ($islatest === true) {
        eval('$result .= "' . $html->islatest . '\n";');
      } elseif ($islatest === false) {
        eval('$result .= "' . $html->requireupdate . '\n";');
      } else {
        eval('$result .= "'. $html->errorgetlatest . '\n";');
      }
      break;
      
      case 'engine':
      $result = $html->checkallscript;
      $checkboxes = '';
      $item = $html->engineitem;
      $item .= "\n";
      
      $inifile = parse_ini_file($paths['lib'] . 'install' . DIRECTORY_SEPARATOR . 'classes.ini', true);
      $ini = &$inifile['items'];
      foreach ($ini as $name => $value) {
        $checkboxes .= sprintf($item, $name, $value, !isset($classes->items[$name]) ? $checked : '');
      }
      
      foreach ($classes->items as $name => $value) {
        if (isset($ini[$name])) continue;
        $checkboxes .= sprintf($item, $name, $value[0], '');
      }
      
      eval('$result .= "'. $html->engineform  . '\n";');
      return $this->FixCheckall($result);
      
      case 'backup':
      $result= $html->partialform;
      $result .= $html->fullbackupform;
      $result .=  $html->uploadform;
      $result .= $this->GetBackupFilelist();
      eval('$result = "'. $result . '\n";');
      break;
      
      case 'download':
      if (isset($_GET['filename'])) {
        $filename = $_GET['filename'];
        if ($s = @file_get_contents($paths['backup'] . $filename)) {
          $this->SendZip($s, $filename);
        }
      }
      $result = $this->notfound;
      break;
      
      case 'delete':
      $filename = $paths['backup'] . $_GET['filename'];
      if (!@file_exists($filename)) {
        $result = $this->notfound;
      } elseif ($this->confirmed) {
        @unlink($filename);
        eval('$result = "'. $html->backupdeleted . '\n";');
      } else {
        eval('$result .= "'. $html->confirmdelete . '\n";');
      }
      break;
      
      case 'run':
      $script = isset($_POST['content']) ? $_POST['content'] : '';
      $result = $html->runform($this->ContentToForm($script));
      break;
    }
    
    $result = str_replace("'", '"', $result);
    return $result;
  }
  
  private function HandleUpdate($req) {
    $html = THtmlResource::Instance();
    $lang = TLocal::Instance();
    if (isset($req['autoupdate'])) {
      $updater = TUpdater::Instance();
      $result = $updater->AutoUpdate();
      return "<h2>$result</h2>\n";
    } elseif (isset($req['update'])) {
      $updater = &TUpdater::Instance();
      $updater->Update();
      eval('$result = "'. $html->successupdated . '\n";');
      return $result;
    }
    return '';
  }
  
  public function ProcessForm() {
    global $classes, $Options, $Urlmap, $paths, $domain;
    $html = &THtmlResource::Instance();
    $html->section = $this->basename;
    $lang = &TLocal::Instance();
    
    switch ($this->arg) {
      case null: return $this->HandleUpdate($_POST);
      
      case 'engine':
      $inifile = parse_ini_file($paths['lib'] . 'install' . DIRECTORY_SEPARATOR . 'classes.ini', true);
      $ini = &$inifile['items'];
      $lang->section = $this->basename;
      $classes->Lock();
      foreach ($_POST as $name => $value) {
        if ( isset($ini[$name]) || isset($classes->items[$name])) {
          switch ($_POST['submit']) {
            case $lang->install:
            $classes->Add($name, $ini[$name]);
            break;
            
            case $lang->uninstall:
            $plugins = TPlugins::Instance();
            $plugins->deleteclass($name);
            $classes->delete($name);
            break;
            
            case $lang->reinstall:
            $classes->Reinstall($name);
            break;
          }
        }
      }
      $classes->Unlock();
      break;
      
      case 'backup':
      $admin = &TRemoteAdmin::Instance();
      extract($_POST);
      switch ($dest) {
        case 'upload':
        if (!is_uploaded_file($_FILES["filename"]["tmp_name"])) {
          return $html->attack($_FILES["filename"]["name"]);
        }
        
        $url = $Options->url;
        $admin->Upload(file_get_contents($_FILES["filename"]["tmp_name"]));
        if (isset($saveurl)) {
          $Options->Load();
          $Options->Seturl($url);
        }
        $Urlmap->ClearCache();
        @header('Location: http://' . $_SERVER['HTTP_HOST'] .  $_SERVER['REQUEST_URI']);
        exit();
        
        case 'downloadpartial':
        $content = $admin->GetPartialBackup(isset($plugins), isset($theme), isset($lib));
        $this->SendZip($content);
        
        case 'fullbackup':
        $content = $admin->GetFullBackup();
        $this->SendZip($content);
      }
      break;
      
      case 'run':
      $result = eval($_POST['content']);
      return $result;
      break;
    }
    
  }
  
  private function SendZip(&$content, $filename = '') {
    global $domain;
    //@file_put_contents("$domain.zip", $content);
    if ($filename == '') $filename = str_replace('.', '-', $domain) . date('-Y-m-d') . '.zip';
    @header("HTTP/1.1 200 OK");
    @header("Content-type: application/octet-stream");
    @header("Content-Disposition: attachment; filename=$filename");
    @header("Content-Length: ".strlen($content));
    @ header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    
    echo $content;
    exit();
  }
  
  private function GetBackupFilelist() {
    global $Options, $paths;
    $html = &THtmlResource::Instance();
    $html->section = $this->basename;
    $lang = &TLocal::Instance();
    
    eval('$result = "'. $html->backupheader . '\n";');
    $filelist = tfiler::getfiles($paths['backup']);
    foreach($filelist as $filename) {
      if (!preg_match('/\.zip$/',  $filename)) continue;
      eval('$result .= "'. $html->backupitem . '\n";');
    }
    eval('$result .= "'. $html->backupfooter . '\n";');;
    return $result;
  }
  
}//class
?>