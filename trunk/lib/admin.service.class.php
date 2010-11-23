<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminservice extends tadminmenu {
  public static function instance($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  
  public function getcontent() {
    $html = $this->html;
    $result = '';
    $args = targs::instance();
    
    switch ($this->name) {
      case 'service':
      $result .= $this->doupdate($_GET);
      $args->postscount = litepublisher::$classes->posts->count;
      $args->commentscount = litepublisher::$classes->commentmanager->count;
      $result .= $html->info($args);
      $updater = tupdater::instance();
      $islatest= $updater->islatest();
      if ($islatest === true) {
        $result .= $html->h3->islatest;
      } elseif ($islatest === false) {
        $result .= $html->requireupdate();
      } else {
        $result .= $html->h2->errorservice;
      }
      break;
      
      case 'engine':
      $result = '';
      $checkboxes = '';
      $item = $html->engineitem;
      $item .= "\n";
      
      $inifile = parse_ini_file(litepublisher::$paths->lib . 'install' . DIRECTORY_SEPARATOR . 'classes.ini', true);
      $ini = &$inifile['items'];
      foreach ($ini as $name => $value) {
        $checkboxes .= sprintf($item, $name, $value, !isset(litepublisher::$classes->items[$name]) ? "checked='checked'" : '');
      }
      
      foreach (litepublisher::$classes->items as $name => $value) {
        if (isset($ini[$name])) continue;
        $checkboxes .= sprintf($item, $name, $value[0], '');
      }
      
      $args->checkboxes = $checkboxes;
      $result .= $html->engineform($args);
      return $html->fixquote($result);
      
      case 'backup':
      if (empty($_GET['action'])) {
        $result= $html->partialform();
        $result .= $html->fullbackupform();
        if (dbversion) $result .= $html->sqlbackupform();
        $result .=  $html->uploadform();
        $result .= $this->getbackupfilelist();
      } else {
        $filename = $_GET['id'];
        if (strpbrk ($filename, '/\<>')) return $this->notfound;
        if (!file_exists(litepublisher::$paths->backup . $filename)) return $this->notfound;
        switch ($_GET['action']) {
          case 'download':
          if ($s = @file_get_contents(litepublisher::$paths->backup . $filename)) {
            $this->sendfile($s, $filename);
          } else {
            return $this->notfound;
          }
          break;
          
          case 'delete':
          if ($this->confirmed) {
            @unlink(litepublisher::$paths->backup . $filename);
            return $html->h2->backupdeleted;
          } else {
            $args->adminurl = $this->adminurl;
            $args->id=$_GET['id'];
            $args->action = 'delete';
            $args->confirm = sprintf('%s %s?', $this->lang->confirmdelete, $_GET['id']);
            $result .= $html->confirmform($args);
          }
        }
      }
      break;
      
      case 'run':
$args->formtitle = $this->lang->runhead;
      $args->content = isset($_POST['content']) ? $_POST['content'] : '';
      $result = $html->adminform('[editor=content]', $args);
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
    $html = $this->html;
    
    switch ($this->name) {
      case 'service':
      return $this->doupdate($_POST);
      
      case 'engine':
      $inifile = parse_ini_file(litepublisher::$paths->lib . 'install' . DIRECTORY_SEPARATOR . 'classes.ini', true);
      $ini = &$inifile['items'];
      $lang = tlocal::instance('service');
      litepublisher::$classes->lock();
      foreach ($_POST as $name => $value) {
        if ( isset($ini[$name]) || isset(litepublisher::$classes->items[$name])) {
          switch ($_POST['submit']) {
            case $lang->install:
            litepublisher::$classes->add($name, $ini[$name]);
            break;
            
            case $lang->uninstall:
            $plugins = tplugins::instance();
            $plugins->deleteclass($name);
            litepublisher::$classes->delete($name);
            break;
            
            case $lang->reinstall:
            litepublisher::$classes->reinstall($name);
            break;
          }
        }
      }
      litepublisher::$classes->unlock();
      break;
      
      case 'backup':
      $backuper = tbackuper::instance();
      extract($_POST);
      switch ($dest) {
        case 'upload':
        if (!is_uploaded_file($_FILES["filename"]["tmp_name"])) {
          return $html->attack($_FILES["filename"]["name"]);
        }
        
        if (strpos($_FILES["filename"]["name"], '.sql')) {
          $backuper->uploaddump(file_get_contents($_FILES["filename"]["tmp_name"]));
        } else {
          $url = litepublisher::$site->url;
          if (dbversion) $dbconfig = litepublisher::$options->dbconfig;
          $backuper->upload(file_get_contents($_FILES["filename"]["tmp_name"]));
          if (isset($saveurl)) {
            litepublisher::$options->load();
            litepublisher::$options->lock();
            litepublisher::$options->seturl($url);
            if (dbversion) litepublisher::$options->dbconfig = $dbconfig;
            litepublisher::$options->unlock();
            litepublisher::$options->savemodified();
          }
        }
        ttheme::clearcache();
        @header('Location: http://' . $_SERVER['HTTP_HOST'] .  $_SERVER['REQUEST_URI']);
        exit();
        
        case 'downloadpartial':
        $content = $backuper->getpartial(isset($plugins), isset($theme), isset($lib));
        $this->sendfile($content);
        
        case 'fullbackup':
        $content = $backuper->getfull();
        $this->sendfile($content);
        
        case 'sqlbackup':
        $content = gzencode($backuper->getdump());
        $this->sendfile($content, litepublisher::$domain . date('-Y-m-d') . '.sql.gz');
      }
      break;
      
      case 'run':
      $result = eval($_POST['content']);
      return $result;
      break;
    }
    
  }
  
  private function sendfile(&$content, $filename = '') {
    //@file_put_contents(litepublisher::$domain . ".zip", $content);
    if ($filename == '') $filename = str_replace('.', '-', litepublisher::$domain) . date('-Y-m-d') . '.tar.gz';
    @header("HTTP/1.1 200 OK");
    @header("Content-type: application/octet-stream");
    @header("Content-Disposition: attachment; filename=$filename");
    @header("Content-Length: ".strlen($content));
    @ header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    
    echo $content;
    exit();
  }
  
  private function getbackupfilelist() {
    $html = $this->html;
    $result = $html->backupheader();
    $args = targs::instance();
    $args->adminurl = $this->adminurl;
    if ($list = glob(litepublisher::$paths->backup . '*.gz')) {
      foreach($list as $filename) {
        $args->filename = basename($filename);
        $result .= $html->backupitem($args);
      }
    }
    $result .= $html->backupfooter;
    return $result;
  }
  
}//class
?>