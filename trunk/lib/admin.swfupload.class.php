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
    if ( 'POST' != $_SERVER['REQUEST_METHOD'] ) {
      return "<?php
      @header('Allow: POST');
      @header('HTTP/1.1 405 Method Not Allowed', true, 405);
      @header('Content-Type: text/plain');
      ?>";
    }

if (!$this->auth()) return $this->error500('Unauthorized');

    $files = tfiles::instance();
      if (!is_uploaded_file($_FILES["filename"]["tmp_name"])) return sprintf($this->html->h2->attack, $_FILES["filename"]["name"]);
      
      $overwrite  = isset($_POST['overwrite']);
      $parser = tmediaparser::instance();
      $parser->uploadfile($_FILES["filename"]["name"], $_FILES["filename"]["tmp_name"], $_POST['title'], $overwrite);
}

}//class
?>