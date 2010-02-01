<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminswfupload extends tevents {
  public static function instance() {
    return getinstance(__class__);
  }

private function error500($msg) {
      return "<?php
      @header('HTTP/1.1 500 Internal Server Error', true, 500);
      @header('Content-Type: text/plain');
      ?>" . $msg;
}

private function auth() {
global $options;
if (empty($_POST['admincookie'])) return false;
$_COOKIE['admin'] = $_POST['admincookie'];
    $options->admincookie = $options->cookieenabled && $options->authcookie();
if (!$options->admincookie) return false;
      if (($options->group == 'admin') || ($options->group == 'editor') return true;
      $groups = tusergroups::instance();
return $groups->hasright($options->group, 'editor');
}

  public function request() {
global $options;
$this->cache = false;
    if ( 'POST' != $_SERVER['REQUEST_METHOD'] ) {
      return "<?php
      @header('Allow: POST');
      @header('HTTP/1.1 405 Method Not Allowed', true, 405);
      @header('Content-Type: text/plain');
      ?>";
    }

if (!$this->auth()) return $this->error500('Unauthorized');

	if (!isset($_FILES["Filedata"]) || !is_uploaded_file($_FILES["Filedata"]["tmp_name"]) || $_FILES["Filedata"]["error"] != 0) return $this->error500('Something wrong in post data");

      $parser = tmediaparser::instance();
      $id = $parser->uploadfile($_FILES["Filedata"]["name"], $_FILES["Filedata"]["tmp_name"], '', false);
$files = tfiles::instance();
$item = $files->getitem($id);
$result = "$id\n{$item['filename']}\n";
$result .= str_replace(

return "<?php
    @Header( 'Cache-Control: no-cache, must-revalidate');
    @Header( 'Pragma: no-cache');
        @header('Content-Type: text/plain; charset=utf-8');
    @ header('Last-Modified: ' . date('r'));
    @header('X-Pingback: $options->url/rpc.xml');
?>" . $result;
}

}//class
?>