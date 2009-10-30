<?php
//require_once($paths['lib']. 'kernel.php');

class TInstaller extends TDataClass {
  public $language;
  public $mode;
  public $lite;
  public $resulttype;
  
  public static function &Instance() {
    return GetInstance(__class__);
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
        $html = &THtmlResource::Instance();
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
    global $classes, $paths;
    $this->CheckFolders();
    //$classes->Install();
    //because TClasses cant self install
    require_once($paths['lib'] . 'install' . DIRECTORY_SEPARATOR . 'classes.install.php');
    TClassesInstall($classes);
    
    //require_once($paths['lib'] . DIRECTORY_SEPARATOR . 'optionsclass.install.php');
    $password = $this->InstallOptions();
    $this->InstallClasses();
    return $password;
  }
  
  public function Install() {
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
      return $this->PrintForm();
    }
    
    $password = $this->FirstStep();
    $this->ProcessForm(
    $_POST['email'],
    $_POST['name'],
    $_POST['description'],
    isset($_POST['checkrewrite'])
    );
    
    return $this->CreateDefaultItems($password);
  }
  
  public function ProcessForm($email, $name, $description, $rewrite) {
    global $Options;
    $Options->Lock();
    $Options->email = $email;
    $Options->name = $name;
    $Options->description = $description;
    $Options->fromemail = 'litepublisher@' . $_SERVER['SERVER_NAME'];
    $this->CheckApache($rewrite);
if ($options->q == '&') $options->data['url'] .= '/index.php?url=';
    $Options->Unlock();
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
    global $Options;
    if ($rewrite || (function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules()))) {
      $Options->q = '?';
    } else {
      $Options->q = '&';
    }
  }
  
  public function ExtractSubdir() {
    if (isset($_GET) && (count($_GET) > 0) && ($i = strpos($_SERVER['REQUEST_URI'], '?'))) {
      $_SERVER['REQUEST_URI']= substr($_SERVER['REQUEST_URI'], 0, $i);
    }
    
    if (preg_match('/index\.php$/', $_SERVER['REQUEST_URI'])) {
      $_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], 0, strlen(   $_SERVER['REQUEST_URI']) - strlen('index.php'));
    }
    
    if (preg_match('/install\.php$/', $_SERVER['REQUEST_URI'])) {
      $_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], 0, strlen(   $_SERVER['REQUEST_URI']) - strlen('install.php'));
    }
    
    return rtrim($_SERVER['REQUEST_URI'], '/');
  }
  
  public function  InstallOptions() {
    global $paths, $Options, $domain;
    $Options = TOptions::Instance();
    $Options->Lock();
    $Options->subdir = $this->ExtractSubdir();
    $Options->url = 'http://'. strtolower($_SERVER['HTTP_HOST'])  . $Options->subdir;
    $Options->files =$Options->Data['url'];
    $Options->q = '?';
    
    $Options->language = $this->language;
    tlocal::loadlang('admin');
    $Options->timezone = TLocal::$data['installation']['timezone'];
    $Options->dateformat = '';
    $Options->keywords = "blog";
    $Options->login = "admin";
    $Options->password = "";
    $Options->realm = "Admin panel";
    $Options->login = 'admin';
    $password = md5(secret. uniqid( microtime()));
    $Options->SetPassword($password);
    
    $Options->email = "yarrowsoft@gmail.com";
    $Options->mailer = "";
    $Options->Data['CacheEnabled'] = true;
    $Options->CacheExpired	= 3600;
    $Options->Data['postsperpage'] = 10;
    $Options->DefaultCommentStatus = "approved";
    $Options->commentsdisabled = false;
    $Options->commentsenabled = true;
    $Options->pingenabled = true;
    $Options->commentpages = true;
    $Options->commentsperpage = 100;
    $Options->version = TUpdater::GetVersion();
    $Options->echoexception = true;
    
    $Options->Unlock();
    return $password;
  }
  
  public function InstallClasses() {
    global  $classes, $Options;
    $Options->Lock();
    $Urlmap = TUrlmap::Instance();
    $GLOBALS['Urlmap'] = &TUrlmap::Instance();
    $Urlmap->Lock();
    $posts = TPosts::Instance();
    $posts->Lock();
    foreach( $classes->items as $ClassName => $Info) {
      $Obj = GetInstance($ClassName);
      if (method_exists($Obj, 'Install')) $Obj->Install();
    }
    $posts->Unlock();
    $Urlmap->Unlock();
    $Options->Unlock();
    
    //install pda
    global $paths;
    $pda = $paths['cache'] . 'pda' ;
    copy($paths['data']. 'template.php', $paths['data'] . 'template.pda.php');
    chmod($paths['data']. 'template.pda.php', 0666);
    
    copy($paths['data']. 'templatecomment.php', $paths['data'] . 'templatecomment.pda.php');
    chmod($paths['data']. 'templatecomment.pda.php', 0666);
    
  }
  
  public function PrintForm() {
    $this->LoadLang();
    $form = $this->GetLangForm();
    $html = &THtmlResource::Instance();
    $html->section = 'installation';
    $lang = &TLocal::Instance();
    if (function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules())) {
      $checkrewrite   = '';
    } else {
      eval('$checkrewrite =  "'. $html->checkrewrite . '\n";');
    }
    eval('$form .= "'. $html->installform. '\n";');
    echo SimplyHtml(TLocal::$data['installation']['title'],  $form);
  }
  
  private function GetLangForm() {
    $langs = array(
    'en' => 'English',
    'ru' => 'Russian'
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
  
  public function CreateWidgets() {
    $arch = &TArchives::Instance();
    $arch->lite = $this->lite;
    $Template = &TTemplate::Instance();
    $Template->Lock();
    //sitebar1
    if (!$this->lite) $Template->AddWidget('TCategories', 'echo', 'categories', TLocal::$data['default']['categories'], 0, 0);
    $Template->AddWidget('TLinksWidget', 'echo', 'links', TLocal::$data['default']['links'],-1, 0);
    $Template->AddWidget('TArchives', 'echo', 'archives', TLocal::$data['default']['archives'],-1, 0);
    $Template->AddWidget('TFoaf', 'echo', 'myfriends', TLocal::$data['default']['myfriends'],-1, 0);
    
    //sitebar2
    $Template->AddWidget('TPosts', 'echo', 'recentposts', TLocal::$data['default']['recentposts'], 0, 1);
    $Template->AddWidget('TCommentManager', 'include', 'recentcomments', TLocal::$data['default']['recentcomments'], 1, 1);
    $Template->AddWidget('TMetaWidget', 'echo', 'meta', TLocal::$data['default']['meta'], 2, 1);
    
    //footer
    $html = &THtmlResource::Instance();
    $html->section = 'installation';
    $lang = &TLocal::Instance();
    
    eval('$Template->footer = "'. $html->footer . '";');
    $Template->footer  .= $html->stat;
    $Template->Unlock();
  }
  
  public function CreateMenuItem() {
    $html = &THtmlResource::Instance();
    $html->section = 'installation';
    $lang = &TLocal::Instance();
    
    $Menu = &TMenu::Instance();
    $Item = TContactForm::Instance();
    $Item->order = 10;
    $Item->title =  TLocal::$data['installation']['contacttitle'];
    eval('$Item->content = "'. $html->contactform . '\n";');
    
    $Menu->Add($Item);
  }
  
  public function CreateFirstPost() {
    global $Options;
    $html = &THtmlResource::Instance();
    $html->section = 'installation';
    $lang = &TLocal::Instance();
    
    $post = TPost::Instance(0);
    $post->title = $lang->posttitle;
    $post->catnames = $lang->postcategories;
    $post->tagnames = $lang->posttags;
    eval('$post->content = "'. $lang->postcontent . '";');
    
    $posts = &TPosts::Instance();
    $posts->Add($post);
    
    $users = &TCommentUsers::Instance();
    $userid = $users->Add($lang->author, $lang->email, $lang->homeurl);
    
    $CommentManager = &TCommentManager::Instance();
    $CommentManager->AddToPost($post, $userid,$lang->postcomment);
  }
  
  public static function SendEmail($password) {
    global $Options;
    tlocal::loadlang('admin');
    $lang = &TLocal::$data['installation'];
    $url = $Options->url . $Options->home;
    $login = $Options->login;
    eval('$body = "' . $lang['body'] . '";');
    
    TMailer::SendMail('', $Options->fromemail,
    '', $Options->email, $lang['subject'], $body);
  }
  
  public function PrintCongratulation($password) {
    global $Options;
    $html = &THtmlResource::Instance();
    $html->section = 'installation';
    $lang = &TLocal::Instance();
    
    $url = $Options->url . $Options->home;
    eval('$content = "'. $html->congratulation . '";');
    
    echo SimplyHtml($Options->name, $content);
  }
  
  public function Uninstall() {
    global $paths;
    TFiler::DeleteFiles($paths['data'], true);
    TFiler::DeleteFiles($paths['cache'], true);
    TFiler::DeleteFiles($paths['files'], true);
  }
  
  private function LoadLang() {
    global $paths;
    $GLOBALS['Options'] = &$this;
    require_once($paths['lib'] . 'filerclass.php');
    require_once($paths['lib'] . 'localclass.php');
    require_once($paths['lib'] . 'htmlresource.php');
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