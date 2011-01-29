<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminservice extends tadminmenu {
  public static function instance($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function getcontent() {
    $result = '';
    $html = $this->html;
    $args = targs::instance();
    
    switch ($this->name) {
      case 'service':
      $result .= $this->doupdate($_GET);
      $args->postscount = litepublisher::$classes->posts->count;
      $args->commentscount = litepublisher::$classes->commentmanager->count;
      $result .= $html->info($args);
      $updater = tupdater::instance();
      $islatest= $updater->islatest();
      if ($islatest === false) {
        $result .= $html->h2->errorservice;
      } elseif ($islatest <= 0) {
        $result .= $html->h3->islatest;
      } else {
        $args->loginform = $this->getloginform();
        $result .= $html->requireupdate($args);
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
        $args->loginform = $this->getloginform();
        $args->plugins = false;
        $args->theme = false;
        $args->lib = false;
        $args->dbversion = dbversion ? '' : 'disabled="disabled"';
        $args->saveurl = true;
        $result= $html->backupform($args);
        
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
      if (!$this->checkbackuper()) return $html->h3->erroraccount;
      $updater = tupdater::instance();
      if ($updater->autoupdate())       return $html->h2->successupdated;
      return sprintf('<h3>%s</h3>', $updater->result);
    } elseif (isset($req['update'])) {
      $updater = tupdater::instance();
      $updater->update();
      return $html->h2->successupdated;
    }
    return '';
  }
  
  public function checkbackuper() {
    $backuper = tbackuper::instance();
    if ($backuper->filertype == 'file') return true;
    $host = tadminhtml::getparam('host', '');
    $login = tadminhtml::getparam('login', '');
    $password = tadminhtml::getparam('password', '');
    if (($host == '') || ($login == '') || ($password == '')) return '';
    
    return $backuper->connect($host, $login, $password);
  }
  
  public function getloginform() {
    $backuper = tbackuper::instance();
    //$backuper->filertype = 'ftp';
    if ($backuper->filertype == 'file') return '';
    $html = $this->html;
    $args = targs::instance();
    $acc = $backuper->filertype == 'ssh2' ? $html->h3->ssh2account : $html->h3->ftpaccount;
    $args->host = tadminhtml::getparam('host', '');
    $args->login = tadminhtml::getparam('login', '');
    $args->password = tadminhtml::getparam('pasword', '');
    return $acc. $html->parsearg('[text=host] [text=login] [password=password]', $args);
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
      if (!$this->checkbackuper()) return $html->h3->erroraccount;
      $backuper = tbackuper::instance();
      extract($_POST, EXTR_SKIP);
      if (isset($upload)) {
        if (!is_uploaded_file($_FILES["filename"]["tmp_name"])) {
          return $html->attack($_FILES["filename"]["name"]);
        }
        
        if (strpos($_FILES['filename']['name'], '.sql')) {
          $backuper->uploaddump(file_get_contents($_FILES["filename"]["tmp_name"]), $_FILES["filename"]["name"]);
        } else {
          $url = litepublisher::$site->url;
          if (dbversion) $dbconfig = litepublisher::$options->dbconfig;
          $backuper->upload(file_get_contents($_FILES["filename"]["tmp_name"]), $backuper->getarchtype($_FILES["filename"]["name"]));
          if (isset($saveurl)) {
            $storage = new tdata();
            $storage->basename = 'storage';
            $storage->load();
            $storage->data['site'] = litepublisher::$site->data;
            if (dbversion) $data->data['options']['dbconfig'] = $dbconfig;
            $storage->save();
          }
        }
        ttheme::clearcache();
        @header('Location: http://' . $_SERVER['HTTP_HOST'] .  $_SERVER['REQUEST_URI']);
        exit();
        
      } elseif (isset($downloadpartial)) {
        $filename = str_replace('.', '-', litepublisher::$domain) . date('-Y-m-d') . $backuper->getfiletype();
        $content = $backuper->getpartial(isset($plugins), isset($theme), isset($lib));
        $this->sendfile($content, $filename);
        
      } elseif (isset($fullbackup)) {
        $filename = str_replace('.', '-', litepublisher::$domain) . date('-Y-m-d') . $backuper->getfiletype();
        $content = $backuper->getfull();
        $this->sendfile($content);
        
      } elseif (isset($sqlbackup)) {
        $content = $backuper->getdump();
        $filename = litepublisher::$domain . date('-Y-m-d') . '.sql';
        
        switch ($backuper->archtype) {
          case 'tar':
          tbackuper::include_tar();
          $tar = new tar();
          $tar->addstring($content, $filename, 0644);
          $content = $this->tar->savetostring(true);
          $filename .= '.tar.gz';
          unset($tar);
          break;
          
          case 'zip':
          tbackuper::include_zip();
          $zip = new zipfile();
          $zip->addFile($content, $filename);
          $content = $zip->file();
          $filename .= '.zip';
          unset($zip);
          break;
          
          default:
          $content = gzencode($content);
          $filename .= '.gz';
          break;
        }
        
        $this->sendfile($content, $filename);
      }
      break;
      
      case 'run':
      $result = eval($_POST['content']);
      return $result;
      break;
    }
    
  }
  
  private function sendfile(&$content, $filename) {
    //@file_put_contents(litepublisher::$domain . ".zip", $content);
    if ($filename == '') $filename = str_replace('.', '-', litepublisher::$domain) . date('-Y-m-d') . '.zip';
    header('HTTP/1.1 200 OK', true, 200);
    header('Content-type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . $filename);
    header('Content-Length: ' .strlen($content));
    header('Last-Modified: ' . date('r'));
    
    echo $content;
    exit();
  }
  
  private function getbackupfilelist() {
    $html = $this->html;
    $result = $html->backupheader();
    $args = targs::instance();
    $args->adminurl = $this->adminurl;
    if ($list = glob(litepublisher::$paths->backup . '*.gz;*.zip')) {
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