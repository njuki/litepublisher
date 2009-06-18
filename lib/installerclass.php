<?php
require_once($paths['lib']. 'dataclass.php');

class TInstaller extends TDataClass {
 public $mode;
 public $lite;
 public $resulttype;
 
 public static function &Instance() {
  return GetInstance(__class__);
 }
 
 public function DefineMode () {
  $this->mode = 'form';
  $this->lite = false;
  
  if (isset($_GET) && (count($_GET) > 0)) {
   $_SERVER['REQUEST_URI']= substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?'));
  }
  if (!empty($_GET['mode'])) $this->mode = $_GET['mode'];
  if (!empty($_GET['lite'])) $this->lite = $_GET['lite'] == 'true';
  if (!empty($_GET['resulttype'])) $this->resulttype = $_GET['resulttype'];
 }
 
 public function AutoInstall() {
  global $Options;
  $this->CanInstall();
  $Options->Lock();
  $this->FirstStep();
  
  $password = md5(secret. uniqid( microtime()));
  $Options->SetPassword($password);
  
  $Options->installed = true;
  $Options->Unlock();
  $this->CreateDefaultItems($password);
  if ($this->mode == 'remote') {
   $this->OutputResult($password);
  }
 }
 
 public function OutputResult($password) {
  global $Options, $paths;
  if ($this->mode == 'remote') {
   $result = array(
   'url' => strtolower($_SERVER['HTTP_HOST']) ,
   'login' => $Options->login,
   'password' => $password,
   'email' => $Options->email,
   'name' => $Options->name,
   'description' => $Options->description
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
    eval('$xml = "'. TLocal::$data['xmlrpc']['xml'] . '\n";');
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
  TLocal::LoadLangFile('install');
  
  if ($this->mode != 'remote') {
   $this->PrintCongratulation($password);
  }
  $Urlmap = &TUrlmap::Instance();
  $Urlmap->Lock();
  $this->CreateWidgets();
  $this->CreateMenuItem();
  if (!$this->lite) $this->CreateFirstPost();
  $Urlmap->Unlock();
  $this->SendEmail($password);
  return $password;
 }
 
 public function CanInstall() {
  $this->CheckSystem();
  $this->CheckFolders();
 }
 
 public function FirstStep() {
  $this->CheckFolders();
  $this->RegisterStandartClasses();
  $this->SetOptions();
  $this->InstallClasses();
 }
 
 public function Install() {
  $this->DefineMode();
  if ($this->mode != 'form') {
   return $this->AutoInstall();
  }
  
  if (isset($_POST) && (count($_POST) > 0)) {
   if (get_magic_quotes_gpc()) {
    foreach ($_POST as $name => $value) {
     $_POST[$name] = stripslashes($_POST[$name]);
    }
   }
   
   $this->FirstStep();
   $this->ProcessForm();
  } else {
   $this->CanInstall();
   $this->PrintForm();
  }
 }
 
 public function ProcessForm() {
  global $Options;
  $Options->Lock();
  $Options->login = 'admin';
  $password = md5(secret. uniqid( microtime()));
  $Options->SetPassword($password);
  
  $Options->email = $_POST['email'];
  $Options->name = $_POST['sitename'];
  $Options->description = $_POST['description'];
  $Options->fromemail = 'blogolet@' . $_SERVER['SERVER_NAME'];
  $this->CheckApache(isset($_POST['checkrewrite']));
  $Options->installed = true;
  $Options->Unlock();
  return $this->CreateDefaultItems($password);
 }
 
 public function RegisterStandartClasses() {
  global $paths;
  $ini = parse_ini_file($paths['libinclude'] . 'classes.ini', false);
  foreach ($ini as $class => $filename) {
   TClasses::$items[$class] = array($filename, '');
  }
  TClasses::Save();
 }
 
 public function CheckFolders() {
  global $paths;
  $this->checkFolder($paths['data']);
  $this->CheckFolder($paths['cache']);
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
  global $Options;
  if ($rewrite || (function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules()))) {
   $Options->q = '?';
  } else {
   $Options->q = '&';
   $Options->url = $Options->url . '/index.php?url=';
  }
 }
 
 public function ExtractSubdir() {
  if (isset($_GET) && (count($_GET) > 0)) {
   $_SERVER['REQUEST_URI']= substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?'));
  }
  
  if (preg_match('/index\.php$/', $_SERVER['REQUEST_URI'])) {
   $_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], 0, strlen(   $_SERVER['REQUEST_URI']) - strlen('index.php'));
  }
  
  
  if (preg_match('/install\.php$/', $_SERVER['REQUEST_URI'])) {
   $_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], 0, strlen(   $_SERVER['REQUEST_URI']) - strlen('install.php'));
  }
  
  return rtrim($_SERVER['REQUEST_URI'], '/');
 }
 
 public function  SetOptions() {
  global $Options, $paths;
  $Options->Lock();
  $ini= parse_ini_file($paths['libinclude']. 'options.ini');
  foreach ($ini as $name => $value) {
   $Options->$name = $value;
  }
  
  $Options->subdir = $this->ExtractSubdir();
  $Options->url = 'http://'. strtolower($_SERVER['HTTP_HOST'])  . $Options->subdir;
  $Options->CacheEnabled = true;
  $Options->CacheExpired	= 3600;
  $Options->postsperpage = 10;
  $Options->commentsenabled = true;
  $Options->pingenabled = true;
  $Options->version = TUpdater::GetVersion();
  
  $Options->Unlock();
 }
 
 public function InstallClasses() {
  global  $Options;
  $Options->Lock();
  $Urlmap = &TUrlmap::Instance();
  $Urlmap->Lock();
  $posts = &TPosts::Instance();
  $posts->Lock();
  foreach( TClasses::$items as $ClassName => $Info) {
   //echo "$ClassName<br>\n";
   $Obj = &GetInstance($ClassName);
   if (method_exists($Obj, 'Install')) $Obj->Install();
  }
  $posts->Unlock();
  $Urlmap->Unlock();
  $Options->Unlock();
  //echo "<pre>\n";
 }
 
 public function PrintForm() {
  global $Options;
  $rewrite = function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules());
  $checkbox = '';
  if (true) {
   //$Options->language == 'ru') {
    if (!$rewrite) $checkbox = '<p><input type="checkbox" name="checkrewrite" />
    <label for="checkrewrite">Я уверен, что установлен apache с модулем mod_rewrite</label></p>';
    
    echo SimplyHtml('Установка Blogolet',
    '<p>Пожалуйста, заполните следующие поля. Обратите особое внимание на адрес E-Mail, на который будет выслан пароль от вашего нового блога</p>
    <form method="post" action="">
    <br clear="all" />
    <p>E-Mail:</p>
    <input name="email" type="text" id="email" value="" />
    <br clear="all" />
    <p>Название блога:</p>
    <input name="sitename" type="text" id="sitename" value="" />
    <br clear="all" />
    <p>Описание блога:</p>
    <input name="description" type="text" id="description" value="" />
    <br clear="all" />
    ' . $checkbox . '
    <br clear="all" /><br clear="all" />
    <input type="submit" name="UPDATE" value="Создать блог" />
    </form>
    ');
   } else {
    if (!$rewrite) $checkbox = '<p><input type="checkbox" name="checkrewrite" />
    <label for="checkrewrite">I sure what apache installed with mod_rewrite</label></p>';
    
    echo SimplyHtml('Welcome to Blogolet',
    '<h2>Information needed</h2>
    <p>Please provide the following information. Double-check your email address before continuing.</p>
    <form method="post" action="index.php?step=2">
    <br clear="all" />
    <p>E-Mail:</p>
    <input name="email" type="text" id="email" value="" />
    <br clear="all" />
    <p>Blog title:</p>
    <input name="sitename" type="text" id="sitename" value="" />
    <br clear="all" />
    <p>Description:</p>
    <input name="description" type="text" id="description" value="" />
    <br clear="all" />
    ' . $checkbox . '
    <br clear="all" /><br clear="all" />
    <input type="submit" name="UPDATE" value="Create blog" />
    </form>
    ');
   }
  }
  
  public function CreateWidgets() {
   $arch = &TArchives::Instance();
   $arch->lite = $this->lite;
   $Template = &TTemplate::Instance();
   $Template->Lock();
   //sitebar1
   if (!$this->lite) $Template->AddWidget('TCategories', 'echo', 0, 0);
   $Template->AddWidget('TLinksWidget', 'echo', -1, 0);
   $Template->AddWidget('TArchives', 'echo', -1, 0);
   //$Template->AddWidget('TTags', 'echo', -1, 1);
   $Template->AddWidget('TFoaf', 'echo', -1, 0);
   
   //sitebar2
   $Template->AddWidget('TPosts', 'echo', 0, 1);
   $Template->AddWidget('TCommentManager', 'include', 1, 1);
   $Template->AddWidget('TMetaWidget', 'echo', 2, 1);
   
   //footer
   $Template->footer = TLocal::$data['footer']['footer'];
   $Template->Unlock();
  }
  
  public function CreateMenuItem() {
   $Menu = &TMenu::Instance();
   $Item = &new TContactForm();
   $Item->order = 10;
   $Item->title =  TLocal::$data['initcontactform']['title'];
   $Item->content = TLocal::$data['initcontactform']['content'];
   //$Item->rawcontent = $Item->content;
   $Menu->Add($Item);
  }
  
  public function CreateFirstPost() {
   global $Options;
   $text = &TLocal::$data['firstpost'];
   $post = &new TPost();
   $post->title = $text['title'];
   $post->catnames = $text['categories'];
   $post->tagnames = $text['tags'];
   eval('$content ="'. $text['content'] . '";');
   $post->content = $content;
   $posts = &TPosts::Instance();
   $posts->Add($post);
   
   $lang = TLocal::$data['blogolet'];
   $users = &TCommentUsers::Instance();
   $userid = $users->Add($lang['author'], $lang['email'], $lang['url']);
   
   $CommentManager = &TCommentManager::Instance();
   $CommentManager->AddToPost($post, $userid,$text['comment']);
  }
  
  public static function SendEmail($password) {
   global $Options;
   $url = $Options->url . $Options->home;
   $login = $Options->login;
   $body = TLocal::$data['firstmail']['body'];
   eval('$body = "' . $body . '";');
   
   TMailer::SendMail('', 'blogolet@'. $_SERVER['SERVER_NAME'],
   '', $Options->email, TLocal::$data['firstmail']['subject'], $body);
  }
  
  public function PrintCongratulation($password) {
   global $Options;
   $url = $Options->url . $Options->home;
   $content = TLocal::$data['congratulation']['content'];
   eval('$content = "'. $content . '";');
   
   echo SimplyHtml($Options->name, $content);
  }
  
  public function Uninstall() {
   global $paths;
   TFiler::DeleteFiles($paths['data'], true);
   TFiler::DeleteFiles($paths['cache'], true);
   TFiler::DeleteFiles($paths['files'], true);
  }
  
 }
 
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