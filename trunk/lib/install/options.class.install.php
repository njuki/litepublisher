<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

/* чтобы предвратить рекурсивный вызов инстал€ции toptions в этом файле нет функции toptionsInstall */
function installoptions($language) {
  $options = toptions::instance();
  $options->lock();
  if (dbversion) {
    $usehost = $_REQUEST['usehost'] == '1';
    $options->data['dbconfig'] = array(
    'driver' => 'mysql',
    'host' => $usehost ? $_REQUEST['dbhost'] : 'localhost',
    'port' => $usehost ? (int) $_REQUEST['dbport'] : 0,
    'dbname' => $_REQUEST['dbname'],
    'login' => $_REQUEST['dblogin'],
    'password' => base64_encode(str_rot13 ($_REQUEST['dbpassword'])),
    'prefix' => $_REQUEST['dbprefix']
    );
    try {
      litepublisher::$db= new tdatabase();
    } catch (Exception $e) {
      die($e->GetMessage());
    }
  }
  
  $options->subdir = getrequestdir();
  $options->fixedurl = true;
  $options->url = 'http://'. strtolower($_SERVER['HTTP_HOST'])  . $options->subdir;
  $options->files =$options->data['url'];
  $options->q = '?';
  
  $options->language = $language;
  tlocal::loadlang('admin');
  $options->timezone = tlocal::$data['installation']['timezone'];
  date_default_timezone_set(tlocal::$data['installation']['timezone']);
  $options->dateformat = '';
  $options->keywords = "blog";
  $options->login = "admin";
  $options->password = "";
  $options->realm = "Admin panel";
  $password = md5uniq();
  $options->SetPassword($password);
  $options->cookieenabled = true;
  $options->cookie = '';
  $options->cookieexpired = 0;
  
  $options->email = "yarrowsoft@gmail.com";
  $options->mailer = "";
  $options->data['cache'] = true;
  $options->expiredcache= 3600;
  $options->ob_cache = true;
  $options->compress = false;
  $options->filetime_offset = tfiler::get_filetime_offset();
  $options->data['perpage'] = 10;
  $options->filtercommentstatus = true;
  $options->DefaultCommentStatus = "approved";
  $options->commentsdisabled = false;
  $options->commentsenabled = true;
  $options->pingenabled = true;
  $options->commentpages = true;
  $options->commentsperpage = 100;
  $options->checkduplicate = true;
  $options->defaultsubscribe = true;
  $options->version = tupdater::getversion();
  $options->echoexception = true;
  $options->parsepost = true;
  $options->usersenabled = false;
  $options->reguser = false;
  $options->icondisabled = false;
  
  $options->unlock();
  return $password;
}

function getrequestdir() {
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

?>