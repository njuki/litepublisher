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
if (!$options->authcookie) return "<?php
    @header('HTTP/1.0 401 Unauthorized', true, 401);
    echo '401 Unauthorized';
    ?>";

    $result = "<?php
    @header('Content-Type: text/html; charset=utf-8');
    @ header('Last-Modified: ' . date('r'));
    @header('X-Pingback: $options->url/rpc.xml');
    ?>";

$id = !empty($_GET['id']) ? $_GET['id'] : 0;
$action = !empty($_GET[['action']) ? $_GET['action'] : '';

switch ($arg) {
case 'comments':
$result .= $this->moderate($id, $action);
break;

default:
return 404;
}    

    return $result;
  }

private function moderate() {
$idpost = !empty($_GET['idpost']) ? $_GET['idpost'] : 0;
$manager = tcommentmanger::instance();

switch ($action) {
case'delete':
return $manager->delete($id, $idpost);

case 'hold':
return $manager->setstatus($idpost, $id, 'hold');

case 'approve':
return $manager->setstatus($idpost, $id, 'approved');

default:
return false;
}
}
  
  public function processform() {
  }
  
}//class
?>