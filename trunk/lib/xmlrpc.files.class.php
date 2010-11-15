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
  

    

  public function geticons($login, $password, $idicon) {
    $this->auth($login, $password, 'editor');
    $result = '';
    $args = targs::instance();
    $files = tfiles::instance();
    if (dbversion) {
      $list = $files->select("media = 'icon'", " order by posted");
      if (!$list) $list = array();
    } else {
      $list= array();
      foreach ($files->items as $id => $item) {
        if ($item['media'] == 'icon') $list[] = $id;
      }
    }
    
    //добавить пустую иконку, то есть отсутствие иконки
    $args->id = 0;
    $args->checked = 0 == $idicon;
    $args->filename = '';
    $args->title = tlocal::$data['common']['empty'];
    $result .= $this->html->radioicon($args);
    
    foreach ($list as $id) {
      $item = $files->getitem($id);
      $args->add($item);
      $args->id = $id;
      $args->checked = $id == $idicon;
      $result .= $this->html->radioicon($args);
    }
    
    return str_replace("'", '"', $result);
  }
  
  public function getthemes($login, $password, $themename) {
    $this->auth($login, $password, 'editor');
    $result = '';
    $args = targs::instance();
    //добавить пустую тему, то есть без отсутствие темы
    $args->checked = '' == $themename;
    $result .= $this->html->emptytheme($args);
    
    $list =    tfiler::getdir(litepublisher::$paths->themes);
    sort($list);
    $parser = tthemeparser::instance();
    foreach ($list as $name) {
      $about = $parser->getabout($name);
      $about['name'] = $name;
      $args->add($about);
      $args->checked = $name == $themename;
      $result .= $this->html->radiotheme($args);
    }
    
    /*  времено запретил - надоделать браузер файлов *.tml выбранной в форме темы ви имя в комбобоксе, а иначе будут бесконечные проблемы
    $args->tmlfile = $tmlfile;
    $result .= $this->html->tmlfile($args);
    */
    
    return $this->html->fixquote($result);
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