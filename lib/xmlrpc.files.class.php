<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class TXMLRPCFiles extends TXMLRPCAbstract {
  private $html;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->html = tadminhtml::instance();
    $this->html->section ='files';
    tlocal::loadlang('admin');
    $lang = tlocal::instance('files');
  }
  
  public function delete($login, $password, $id) {
    $this->auth($login, $password, 'editor');
    $files = tfiles::instance();
    if (!$files->delete((int) $id)) return $this->xerror(404, "File not deleted");
    return true;
  }
  

  // swfupload
  private function error500($msg) {
    return "<?php
    @header('HTTP/1.1 500 Internal Server Error', true, 500);
    @header('Content-Type: text/plain');
    ?>" . $msg;
  }
  
  private function postauth() {
    if (empty($_POST['admincookie'])) return false;
    $_COOKIE['admin'] = $_POST['admincookie'];
    litepublisher::$options->admincookie = litepublisher::$options->cookieenabled && litepublisher::$options->authcookie();
    if (!litepublisher::$options->admincookie) return false;
    if ((litepublisher::$options->group == 'admin') || (litepublisher::$options->group == 'editor')) return true;
    $groups = tusergroups::instance();
    return $groups->hasright(litepublisher::$options->group, 'editor');
  }
  
  public function request() {
    $this->cache = false;
    if ( 'POST' != $_SERVER['REQUEST_METHOD'] ) {
      return "<?php
      @header('Allow: POST');
      @header('HTTP/1.1 405 Method Not Allowed', true, 405);
      @header('Content-Type: text/plain');
      ?>";
    }
    
    //$_POST['admincookie'] = $_COOKIE['admin'];
    if (!$this->postauth()) return $this->error500('Unauthorized');
    
    if (!isset($_FILES["Filedata"]) || !is_uploaded_file($_FILES["Filedata"]["tmp_name"]) || $_FILES["Filedata"]["error"] != 0) return $this->error500('Something wrong in post data');
    
    $parser = tmediaparser::instance();
    $id = $parser->uploadfile($_FILES["Filedata"]["name"], $_FILES["Filedata"]["tmp_name"], '', '', '', false);
    
    return turlmap::htmlheader(false) . $this->getfileitem($id, 'curr');
  }
  
}//class
?>