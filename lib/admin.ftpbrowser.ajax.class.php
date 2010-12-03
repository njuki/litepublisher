<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tajaxftpbrowser extends tevents {

  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'ajaxftpbrowser';
  }
  
  public static function auth() {
    if (!litepublisher::$options->cookieenabled) return self::error403();
    if (!litepublisher::$options->authcookie()) return self::error403();
    if (litepublisher::$options->group != 'admin') {
      $groups = tusergroups::instance();
      if (!$groups->hasright(litepublisher::$options->group, 'admin')) return self::error403();
    }
  }
  
  public function request($arg) {
      if ( 'POST' != $_SERVER['REQUEST_METHOD'] ) {
        return "<?php
        header('Allow: POST');
        header('HTTP/1.1 405 Method Not Allowed', true, 405);
        header('Content-Type: text/plain');
        ?>";
      }
    
    if ($err = self::auth()) return $err;

    return $this->getcontent();
  }
  
  public function getcontent() {
    $theme = tview::instance(tviews::instance()->defaults['admin'])->theme;
    $html = tadminhtml ::instance();
    $html->section = 'editor';
    $lang = tlocal::instance('editor');
    $post = tpost::instance($this->idpost);
    ttheme::$vars['post'] = $post;

$ftp = new tftpfiler::instance($host, $login, $password);
if (!$ftp->connect()) return 'not conne';

if ($list = $ftp->getdir($dir)) {
$result .= '<ul id="ftpfolders">';
foreach ($list as $name => $item) {
if ($item['isdir']) {
$result .= sprintf('<li><a href="%1$s">%1$s</a></li>', $name);
}
}
$result.= '</ul>';
}
    return turlmap::htmlheader(false) . $result;    
}

}//class
