<?php

class tadminservice extends tadminmenuitem {
  public static function instance() {
    return getinstance(__class__);
  }
  

  public function getcontent() {
    global $classes, $options, $paths;
    $html = $this->html;
    $result = '';
$args = targs::instance();
    
    switch ($this->name) {
      case 'service':
      $result .= $this->doupdate($_GET);
$args->postscount = $classes->posts->count;
$args->commentscount = $classes->commentmanager->count;
$result .= $html->info($args);
      $updater = tupdater::instance();
      $islatest= $updater->IsLatest();
      if ($islatest === true) {
$result .= $html->h3->islatest;
      } elseif ($islatest === false) {
$result .= $html->requireupdate();
      } else {
$result .= $html->h2->errorservice;
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
        $checkboxes .= sprintf($item, $name, $value, !isset($classes->items[$name]) ? "checked='checked'" : '');
      }
      
      foreach ($classes->items as $name => $value) {
        if (isset($ini[$name])) continue;
        $checkboxes .= sprintf($item, $name, $value[0], '');
      }
      
$args->checkboxes = $checkboxes;
$result .= $html->engineform($args);
      return $this->FixCheckall($result);
      
      case 'backup':
if (empty($_GET['action'])) }
      $result= $html->partialform();
      $result .= $html->fullbackupform();
      $result .=  $html->uploadform;
      $result .= $this->getbackupfilelist();
} else {
        $filename = $_GET['id'];
if (strpbrk ($filename, '/\<>')) return $this->notfound;
if (!file_exists($paths['backup'] . $filename)) return $this->notfound;
      switch ($_GET['action']) {
      case 'download':
        if ($s = @file_get_contents($paths['backup'] . $filename)) {
          $this->sendfile($s, $filename);
        } else {
return $this->notfound;
}
      break;
      
      case 'delete':
if ($this->confirmed) {
        @unlink($paths['backup'] . $filename);
return $html->h2->backupdeleted;
      } else {
$args->adminurl = $this->adminurl;
$args->id=$_GET['id'];
$args->action = 'delete';
$args->confirm = sprintf('%s %s?', $this->lang->confirmdelete, $_GET['id']);
$result .= $html->confirmdelete($args);
      }
}
}
      break;
      
      case 'run':
      $args->script = isset($_POST['content']) ? $_POST['content'] : '';
      $result = $html->runform($args);
      break;
    }
    
    $result = str_replace("'", '"', $result);
    return $result;
  }
  
  private function doupdate($req) {
    $html = $this->html;
    if (isset($req['autoupdate'])) {
      $updater = tupdater::instance();
      $result = $updater->autoupdate();
      return sprintf("<h2>%s</h2>\n", $result);
    } elseif (isset($req['update'])) {
      $updater = tupdater::instance();
      $updater->update();
return $html->h2->successupdated;
    }
    return '';
  }
  
  public function processform() {
    global $classes, $options, $urlmap, $paths, $domain;
    $html = $this->html;
    
    switch ($this->name) {
      case 'service': 
return $this->doupdate($_POST);

            case 'engine':
      $inifile = parse_ini_file($paths['lib'] . 'install' . DIRECTORY_SEPARATOR . 'classes.ini', true);
      $ini = &$inifile['items'];
      $lang = $this->lang;
      $classes->lock();
      foreach ($_POST as $name => $value) {
        if ( isset($ini[$name]) || isset($classes->items[$name])) {
          switch ($_POST['submit']) {
            case $lang->install:
            $classes->add($name, $ini[$name]);
            break;
            
            case $lang->uninstall:
            $plugins = tplugins::instance();
            $plugins->deleteclass($name);
            $classes->delete($name);
            break;
            
            case $lang->reinstall:
            $classes->reinstall($name);
            break;
          }
        }
      }
      $classes->unlock();
      break;
      
      case 'backup':
      $admin = tremoteadmin::instance();
      extract($_POST);
      switch ($dest) {
        case 'upload':
        if (!is_uploaded_file($_FILES["filename"]["tmp_name"])) {
          return $html->attack($_FILES["filename"]["name"]);
        }
        
        $url = $options->url;
if (dbversion) $dbconfig = $options->dbconfig;
        $admin->upload(file_get_contents($_FILES["filename"]["tmp_name"]));
        if (isset($saveurl)) {
          $options->load();
$options->lock();
          $options->Seturl($url);
if (dbversion) $options->dbconfig = $dbconfig;
$options->unlock();
        }
        $urlmap->clearcache();
        @header('Location: http://' . $_SERVER['HTTP_HOST'] .  $_SERVER['REQUEST_URI']);
        exit();
        
        case 'downloadpartial':
        $content = $admin->GetPartialBackup(isset($plugins), isset($theme), isset($lib));
        $this->sendfile($content);
        
        case 'fullbackup':
        $content = $admin->GetFullBackup();
        $this->sendfile($content);
      }
      break;
      
      case 'run':
      $result = eval($_POST['content']);
      return $result;
      break;
    }
    
  }
  
  private function sendfile(&$content, $filename = '') {
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
  
  private function getbackupfilelist() {
    global $options, $paths;

    $html = $this->html;
$result = $html->backupheader();
$args = targs::instance();
$args->adminurl = $this->adminurl;
    foreach(glob($paths['backup'] . '*.zip') as $filename) {
$args->filename = $filename;
$result .= $html->backupitem($args);
    }
    $result .= $html->backupfooter;
    return $result;
  }
  
}//class
?>