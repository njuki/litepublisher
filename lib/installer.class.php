<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tinstaller extends tdata {
  public $language;
  public $mode;
  public $lite;
  public $resulttype;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function DefineMode () {
    $this->mode = 'form';
    $this->language = $this->GetBrowserLang();
    $this->lite = false;
    
    if (isset($_GET) && (count($_GET) > 0)) {
      $_SERVER['REQUEST_URI']= substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?'));
    }
    
    if (!empty($_GET['lang']))  {
      if (@file_exists(litepublisher::$paths->languages . $_GET['lang'] . '.ini')) $this->language = $_GET['lang'];
    }
    
    if (!empty($_GET['mode'])) $this->mode = $_GET['mode'];
    if (!empty($_GET['lite'])) $this->lite = $_GET['lite'] == 'true';
    if (!empty($_GET['resulttype'])) $this->resulttype = $_GET['resulttype'];
  }
  
  public function AutoInstall() {
    $this->CanInstall();
    $password = $this->FirstStep();
    
    $this->ProcessForm(
    $_GET['email'],
    $_GET['name'],
    $_GET['description'],
    isset($_GET['checkrewrite'])
    );
    
    $this->CreateDefaultItems($password);
    if ($this->mode == 'remote') {
      $this->OutputResult($password);
    }
  }
  
  public function OutputResult($password) {
    if ($this->mode == 'remote') {
      $result = array(
      'url' => litepublisher::$options->url,
      'login' => litepublisher::$options->login,
      'password' => $password,
      'email' => litepublisher::$options->email,
      'name' => litepublisher::$options->name,
      'description' => litepublisher::$options->description
      );
      
      switch ($this->resulttype) {
        case 'serialized' :
        $s = serialize($result);
        $length = strlen($s);
        header('Connection: close');
        header('Content-Length: '.$length);
        header('Content-Type: text/plain');
        header('Date: '.date('r'));
        echo $s;
        exit();
        
        case 'xmlrpc':
        require_once(litepublisher::$paths->libinclude . 'class-IXR.php');
        $r = new IXR_Value($result);
        $resultxml = $r->getXml();
        // Create the XML
        $html = THtmlResource::instance();
        $html->section = 'installation';
        eval('$xml = "'. $html->xmlrpc . '\n";');
        // Send it
        $xml = '<?xml version="1.0"?>'."\n".$xml;
        $length = strlen($xml);
        header('Connection: close');
        header('Content-Length: '.$length);
        header('Content-Type: text/xml');
        header('Date: '.date('r'));
        echo $xml;
        exit();
        
        case 'ini' :
        $ini = '';
        foreach($result as $key => $value) {
          $ini .= "$key = \"$value\"\n";
        }
        
        $length = strlen($ini);
        header('Connection: close');
        header('Content-Length: '.$length);
        header('Content-Type: text/plain');
        header('Date: '.date('r'));
        echo $ini;
        exit();
      }
    }
  }
  
  public function CreateDefaultItems($password) {
    if ($this->mode != 'remote') {
      $this->congratulation($password);
    }
    
    $arch = tarchives::instance();
    $arch->lite = $this->lite;
    
    if (!$this->lite) $this->CreateFirstPost();
    
    $this->SendEmail($password);
    return $password;
  }
  
  public function CanInstall() {
    $this->CheckSystem();
    $this->CheckFolders();
  }
  
  public function FirstStep() {
    $this->CheckFolders();
    if (!defined('dbversion')) {
      if (isset($_REQUEST['dbversion'])) {
        define('dbversion', $_REQUEST['dbversion'] == '1');
      } else {
        define('dbversion', true);
      }
    }
    
    require_once(litepublisher::$paths->lib . 'install' . DIRECTORY_SEPARATOR . 'classes.install.php');
    return installclasses($this->language);
  }
  
  public function install() {
    if (get_magic_quotes_gpc()) {
      if (isset($_POST) && (count($_POST) > 0)) {
        foreach ($_POST as $name => $value) {
          $_POST[$name] = stripslashes($_POST[$name]);
        }
      }
      
      if (isset($_GET) && (count($_GET) > 0)) {
        foreach ($_GET as $name => $value) {
          $_GET[$name] = stripslashes($_GET[$name]);
        }
      }
      
    }
    
    $this->DefineMode();
    if ($this->mode != 'form') return $this->AutoInstall();
    
    if (!isset($_POST) || (count($_POST) <= 1)) {
      $this->CanInstall();
      return $this->wizardform();
    }
    
    $password = $this->FirstStep();
    $this->processform(
    $_POST['email'],
    $_POST['name'],
    $_POST['description'],
    isset($_POST['checkrewrite'])
    );
    
    return $this->CreateDefaultItems($password);
  }
  
  public function processform($email, $name, $description, $rewrite) {
    litepublisher::$options->lock();
    litepublisher::$options->email = $email;
    litepublisher::$options->name = $name;
    litepublisher::$options->description = $description;
    litepublisher::$options->fromemail = 'litepublisher@' . $_SERVER['SERVER_NAME'];
    $this->CheckApache($rewrite);
    if (litepublisher::$options->q == '&') litepublisher::$options->data['url'] .= '/index.php?url=';
    litepublisher::$options->unlock();
  }
  
  public function CheckFolders() {
    $this->checkFolder(litepublisher::$paths->data);
    $this->CheckFolder(litepublisher::$paths->cache);
    $this->CheckFolder(litepublisher::$paths->files);
    $this->CheckFolder(litepublisher::$paths->languages);
    $this->CheckFolder(litepublisher::$paths->plugins);
    $this->CheckFolder(litepublisher::$paths->themes);
  }
  
  public function CheckFolder($FolderName) {
    if(!@file_exists($FolderName)) {
      $up = dirname($FolderName);
      if(!@file_exists($up)) {
        @mkdir($up, 0777);
        @chmod($up, 0777);
      }
      @mkdir($FolderName, 0777);
    }
    @chmod($FolderName, 0777);
    if(!@file_exists($FolderName) && !@is_dir($FolderName)) {
      echo "directory $FolderName is not exists. Please create directory and set permisions to 0777";
      exit();
    }
    $tmp= $FolderName . 'index.htm';
    if (!@file_put_contents($tmp, ' ')) {
      echo "Error write file to the $FolderName folder. Please change permisions to 0777";
      exit();
    }
    @chmod($tmp, 0666);
    //@unlink($tmp);
  }
  
  public function  CheckSystem() {
    if (version_compare(PHP_VERSION, '5.1.4', '<')) {
      echo 'Blogolet requires PHP 5.1.4 or later. You are using PHP ' . PHP_VERSION ;
      exit;
    }
  }
  
  public function CheckApache($rewrite) {
    if ($rewrite || (function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules()))) {
      litepublisher::$options->q = '?';
    } else {
      litepublisher::$options->q = '&';
    }
  }
  
  public function wizardform() {
    $this->loadlang();
    $combobox = $this->getlangcombo();
    $html = THtmlResource::instance();
    $html->section = 'installation';
    $lang = tlocal::instance('installation');
    if (function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules())) {
      $checkrewrite   = '';
    } else {
      eval('$checkrewrite =  "'. $html->checkrewrite . '\n";');
    }
    $dbprefix = strtolower(str_replace(array('.', '-'), '', litepublisher::$domain)) . '_';
    $title = tlocal::$data['installation']['title'];
    $form = file_get_contents(litepublisher::$paths->lib . 'install' . DIRECTORY_SEPARATOR . 'installform.tml');
    $form = str_replace('"', '\"', $form);
    eval('$form = "'. $form . '\n";');
    $this->echohtml(  $form);
  }
  
  private function getlangcombo() {
    $langs = array(
    'en' => 'English',
    'ru' => 'Russian',
    'ua' => 'Ukrain'
    );
    
    $result = '';
    foreach ($langs as $lang => $value) {
      $selected = $lang == $this->language ? 'selected' : '';
      $result .= "<option value='$lang' $selected>$value</option>\n";
    }
    return $result;
  }
  
  public function CreateFirstPost() {
    $html = THtmlResource::instance();
    $html->section = 'installation';
    $lang = tlocal::instance();
    $theme = ttheme::instance();
    
    $post = tpost::instance(0);
    $post->title = $lang->posttitle;
    $post->catnames = $lang->postcategories;
    $post->tagnames = $lang->posttags;
    $post->content = $theme->parse($lang->postcontent);
    $posts = tposts::instance();
    $posts->add($post);
    
    $icons = ticons::instance();
    $cats = tcategories::instance();
    $cats->setvalue($post->categories[0], 'icon', $icons->getid('news'));
    
    $comusers = tcomusers::instance($post->id);
    $userid = $comusers->add($lang->author, $lang->email, $lang->homeurl);
    litepublisher::$classes->commentmanager->addcomment($post->id, $userid,$lang->postcomment);
    
    $plugins = tplugins::instance();
    $plugins->lock();
    //$plugins->add('oldestposts');
    //$plugins->add('adminlinks');
    //$plugins->add('nicedit');
    $plugins->unlock();
  }
  
  public function SendEmail($password) {
    define('mailpassword', $password);
    register_shutdown_function(__class__ . '::sendmail');
  }
  
  public static function sendmail() {
    tlocal::loadlang('admin');
    $lang = &tlocal::$data['installation'];
    $body = sprintf($lang['body'], litepublisher::$options->url, litepublisher::$options->login, mailpassword);
    
    tmailer::sendmail('', litepublisher::$options->fromemail,
    '', litepublisher::$options->email, $lang['subject'], $body);
  }
  
  public function congratulation($password) {
    global  $lang;
    $tml = file_get_contents(litepublisher::$paths->lib . 'install' . DIRECTORY_SEPARATOR . 'install.congratulation.tml');
    $html = THtmlResource::instance();
    $html->section = 'installation';
    $lang = tlocal::instance('installation');
    $args = targs::instance();
    $args->title = litepublisher::$options->name;
    $args->url = litepublisher::$options->url . '/';
    $args->password = $password;
    $content = $html->parsearg($tml, $args);
    $this->echohtml($content);
  }
  
  public function uninstall() {
    tfiler::delete(litepublisher::$paths->data, true);
    tfiler::delete(litepublisher::$paths->cache, true);
    tfiler::delete(litepublisher::$pathsfiles, true);
  }
  
  private function loadlang() {
    litepublisher::$options = $this;
    require_once(litepublisher::$paths->lib . 'filer.class.php');
    require_once(litepublisher::$paths->lib . 'local.class.php');
    require_once(litepublisher::$paths->lib . 'htmlresource.class.php');
    tlocal::loadlang('admin');
  }
  
  private function GetBrowserLang() {
    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
      $result = strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']);
      $result = substr($result, 0, 2);
      if (@file_exists(litepublisher::$paths->languages . "$result.ini")) return $result;
    }
    return 'en';
  }
  
  public function echohtml($html) {
    @header('Content-Type: text/html; charset=utf-8');
    @Header( 'Cache-Control: no-cache, must-revalidate');
    @Header( 'Pragma: no-cache');
    echo $html;
    if (ob_get_level()) ob_end_flush ();
  }
  
}//class

?>