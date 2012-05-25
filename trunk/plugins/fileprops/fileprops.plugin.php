<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tfilepropsplugin extends tplugin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function request($arg) {
    $this->cache = false;
    if (!litepublisher::$options->cookieenabled) return 403;
    if (!litepublisher::$options->authcookie()) return 403;
    if (!litepublisher::$options->hasgroup('editor')) {
      if (!litepublisher::$options->hasgroup('author')) return 403;
    }
    
    if (!isset($_GET['action'])) return 403;
    $files = tfiles::i();
    
    switch ($_GET['action']) {
      case 'get':
      $filename = substr($_GET['filename'], strlen(litepublisher::$site->files . '/files/'));
      if ($id = $files->IndexOf('filename', $filename)) {
        $item = $files->getitem($id);
        if (!litepublisher::$options->can_edit($item['author'])) return 403;
        return turlmap::htmlheader(false) . json_encode($item);
      }
      return 404;
      
      case 'set':
      $id = (int) $_GET['id'];
      if (!$files->itemexists($id)) return 404;
      $item = $files->getitem($id);
      if (!litepublisher::$options->can_edit($item['author'])) return 403;
      $files->edit($id, $_GET['title'], $_GET['description'], $_GET['keywords']);
      $item = $files->getitem($id);
      return turlmap::htmlheader(false) . json_encode($item);
      
      default:
      return 403;
    }
  }
  
}//class