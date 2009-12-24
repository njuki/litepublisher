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
      global $paths;
      if (@file_exists($paths['languages']. $_GET['lang'] . '.ini')) $this->language = $_GET['lang'];
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
    global $options, $paths;
    if ($this->mode == 'remote') {
      $result = array(
      'url' => strtolower($_SERVER['HTTP_HOST']) ,
      'login' => $options->login,
      'password' => $password,
      'email' => $options->email,
      'name' => $options->name,
      'description' => $options->description
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
        require_once($paths['libinclude'] . 'class-IXR.php');
        $r = new IXR_Value($result);
        $resultxml = $r->getXml();
        // Create the XML
        $html = &THtmlResource::instance();
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
    global $paths;
    $this->CheckFolders();
    require_once($paths['lib'] . 'install' . DIRECTORY_SEPARATOR . 'classes.install.php');
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
    global $options;
    $options->lock();
    $options->email = $email;
    $options->name = $name;
    $options->description = $description;
    $options->fromemail = 'litepublisher@' . $_SERVER['SERVER_NAME'];
    $this->CheckApache($rewrite);
    if ($options->q == '&') $options->data['url'] .= '/index.php?url=';
    $options->unlock();
  }
  
  public function CheckFolders() {
    global $paths;
    $this->checkFolder($paths['data']);
    $this->CheckFolder($paths['cache']);
    $this->CheckFolder($paths['cache'] . 'pda' . DIRECTORY_SEPARATOR);
    $this->CheckFolder($paths['files']);
    $this->CheckFolder($paths['languages']);
    $this->CheckFolder($paths['plugins']);
    $this->CheckFolder($paths['themes']);
  }
  
  public function CheckFolder($FolderName) {
    if(!@file_exists($FolderName)) @mkdir($FolderName, 0777);
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
    global $options;
    if ($rewrite || (function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules()))) {
      $options->q = '?';
    } else {
      $options->q = '&';
    }
  }
  
  public function wizardform() {
    $this->loadlang();
    $form = $this->GetLangForm();
    $html = THtmlResource::instance();
    $html->section = 'installation';
    $lang = tlocal::instance('installation');
    if (function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules())) {
      $checkrewrite   = '';
    } else {
      eval('$checkrewrite =  "'. $html->checkrewrite . '\n";');
    }
    eval('$dbform = "'. (dbversion ? $html->dbform : '')  . '";');
    
    eval('$form .= "'. $html->installform. '\n";');
    echo SimplyHtml(tlocal::$data['installation']['title'],  $form);
  }
  
  private function GetLangForm() {
    $langs = array(
    'en' => 'English',
    'ru' => 'Russian',
    'ua' => 'Ukrain'
    );
    
    $result = "<form name='langform' action='' method='get'>
    <p><select name='lang' id='lang'>\n";
    
    foreach ($langs as $lang => $value) {
      $selected = $lang == $this->language ? 'selected' : '';
      $result .= "<option value='$lang' $selected>$value</option>\n";
    }
    
    $result .= "</select>
    <input type='submit' name='submit' value='Change language' /></p>
    </form>";
    
    return $result;
  }
  
  public function CreateFirstPost() {
    global $classes, $options;
    $html = THtmlResource::instance();
    $html->section = 'installation';
    $lang = tlocal::instance();
    
    $post = tpost::instance(0);
    $post->title = $lang->posttitle;
    $post->catnames = $lang->postcategories;
    $post->tagnames = $lang->posttags;
    $post->content = $lang->postcontent;
    $posts = tposts::instance();
    $posts->add($post);
    
    $users = tcomusers::instance($post->id);
    $userid = $users->add($lang->author, $lang->email, $lang->homeurl);
    
    $classes->commentmanager->addcomment($post->id, $userid,$lang->postcomment);
  }
  
  public static function SendEmail($password) {
    global $options;
    tlocal::loadlang('admin');
    $lang = &tlocal::$data['installation'];
    $body = sprintf($lang['body'], $options->url, $options->login, $password);
    
    tmailer::sendmail('', $options->fromemail,
    '', $options->email, $lang['subject'], $body);
  }
  
  public function congratulation($password) {
    global $options, $lang;
    $html = THtmlResource::instance();
    $html->section = 'installation';
    $lang = tlocal::instance('installation');
    $args = targs::instance();
    $args->url = $options->url . '/';
    $args->password = $password;
    $content = $html->congratulation($args);
    
    echo SimplyHtml($options->name, $content);
  }
  
  public function uninstall() {
    global $paths;
    tfiler::delete($paths['data'], true);
    tfiler::delete($paths['cache'], true);
    tfiler::delete($paths['files'], true);
  }
  
  private function loadlang() {
    global $paths;
    $GLOBALS['options'] = $this;
    require_once($paths['lib'] . 'filer.class.php');
    require_once($paths['lib'] . 'local.class.php');
    require_once($paths['lib'] . 'htmlresource.class.php');
    tlocal::loadlang('admin');
  }
  
  private function GetBrowserLang() {
    global $paths;
    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
      $result = strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']);
      $result = substr($result, 0, 2);
      if (@file_exists($paths['languages']. "$result.ini")) return $result;
    }
    return 'en';
  }
  
}//class

function SimplyHtml($title, $content) {
  @header('Content-Type: text/html; charset=utf-8');
  @Header( 'Cache-Control: no-cache, must-revalidate');
  @Header( 'Pragma: no-cache');
  
  return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
  <html xmlns="http://www.w3.org/1999/xhtml">
  <head profile="http://gmpg.org/xfn/11">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>'. $title . '</title>
  </head>
  <body> ' .$content .'</body>
  </html>
  ';
}

?>