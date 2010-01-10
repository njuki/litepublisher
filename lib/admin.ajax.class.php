<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminajax extends tevents {
  public static function instance() {
    return getinstance(__class__);
  }

  public function auth() {
    global $options, $urlmap;
    $auth = tauthdigest::instance();
      if ($s = $auth->checkattack()) return $s;
      if (!$auth->authcookie()) return $urlmap->redir301('/admin/login/');
    if ($options->group != 'admin') {
      $groups = tusergroups::instance();
      if ($this->hasright($this->group)) return 404;
    }
  }
  
  
  public function request($arg) {
global $options;

    $result = "<?php
    @header('Content-Type: text/html; charset=utf-8');
    @ header('Last-Modified: ' . date('r'));
    @header('X-Pingback: $options->url/rpc.xml');
    ?>";
    
    $result .= $this->getcontent($name);
    return $result;

  }
  
  public function processform() {
  }
  
}//class
?>