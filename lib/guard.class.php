<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tguard {

public static function post() {
    if (!isset($_POST) || (count($_POST) == 0)) return false;
if (version_compare(PHP_VERSION, '5.3', '<') && get_magic_quotes_gpc()) {
        foreach ($_POST as $name => $value) {
          $_POST[$name] = stripslashes($_POST[$name]);
        }
      }
return true;
}

  public static function is_xxx() {
    if (isset($_GET['ref'])) {
      $ref = $_GET['ref'];
      $url = $_SERVER['REQUEST_URI'];
      $url = substr($url, 0, strpos($url, '&ref='));
      if ($ref == md5(litepublisher::$secret . litepublisher::$site->url . $url)) return false;
    }
    
    $host = '';
    if (!empty($_SERVER['HTTP_REFERER'])) {
      $p = parse_url($_SERVER['HTTP_REFERER']);
      $host = $p['host'];
    }
    return $host != $_SERVER['HTTP_HOST'];
  }
  
  public static function checkattack() {
    if (litepublisher::$options->xxxcheck  && self::is_xxx()) {
      tlocal::usefile('admin');
      if ($_POST) {
        die(tlocal::get('login', 'xxxattack'));
      }
      if ($_GET) {
        die(tlocal::get('login', 'confirmxxxattack') .
        sprintf(' <a href="%1$s">%1$s</a>', $_SERVER['REQUEST_URI']));
      }
    }
    return false;
  }
  
}//class