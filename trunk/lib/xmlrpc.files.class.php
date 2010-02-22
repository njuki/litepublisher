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
    $this->html = THtmlResource::instance();
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
  
  private function getpagelinks($current, $count) {
    $list = array();
    for ($i = 1; $i <= $count; $i++) {
      if ($i == $current) {
        $list[] = "$i";
      } else {
        $list[] = "<a onclick='post.getpage($i);' title='$i'>$i</a>";
      }
    }
    $result = $this->html->pagelinks();
    return sprintf($result, implode(' | ', $list));
  }
  
  private function get_page($index) {
    $result = '';
    $files = tfiles::instance();
    $perpage = 10;
    if (dbversion) {
      $sql = "parent =0 and media <> 'icon'";
      $sql .= litepublisher::$options->user <= 1 ? '' : " and author = litepublisher::$options->user";
      $count = $files->db->getcount($sql);
    } else {
      $list= array();
      foreach ($files->items as $id => $item) {
        if ($item['parent'] != 0) continue;
        if (litepublisher::$options->user > 1 && litepublisher::$options->user != $item['author']) continue;
        if ($item['media'] == 'icon') continue;
        $list[] = $id;
      }
      $count = count($list);
    }
    
    $from = ($index -1)  * $perpage;
    
    if (dbversion) {
      $list = $files->select($sql, " order by posted desc limit $from, $perpage");
      if (!$list) $list = array();
    } else {
      $list = array_slice($list, $from, $perpage);
    }
    
    $result .= sprintf($this->html->h2->countfiles, $count, $from, $from + count($list));
    $result .= $this->getpagelinks($index, ceil($count / $perpage));
    $page = '';
    foreach ($list as $id) {
      $page .= $this->getfileitem($id, 'pages');
      $page .= "\n";
    }
    
    $result .= sprintf($this->html->page, $page);
    return str_replace("'", '"', $result);
  }
  
  public function getpage($login, $password, $index) {
    $this->auth($login, $password, 'editor');
    return $this->get_page((int) $index);
  }
  
  public function getbrowser($login, $password, $idpost) {
    $this->auth($login, $password, 'editor');
    $args = targs::instance();
    $args->pages = $this->get_page(1);
    $args->currentfiles = $this->getpostfiles((int) $idpost);
    return $this->html->browser($args);
  }
  
  public function geticons($login, $password, $idicon) {
    $this->auth($login, $password, 'editor');
    $result = '';
    $args = targs::instance();
    $files = tfiles::instance();
    if (dbversion) {
      $list = $files->select("media = 'icon' order by posted");
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
      $args->add($about);
      $args->checked = $name == $themename;
      $result .= $this->html->radiotheme($args);
    }
    
    /*  времено запретил - надоделать браузер файлов *.tml выбранной в форме темы ви имя в комбобоксе, а иначе будут бесконечные проблемы
    $args->tmlfile = $tmlfile;
    $result .= $this->html->tmlfile($args);
    */
    
    return str_replace("'", '"', $result);
  }
  
  
  private function getpostfiles($idpost) {
    $result = '';
    $post = tpost::instance((int) $idpost);
    foreach ($post->files as $id) {
      $result .= $this->getfileitem($id, 'curr');
    }
    return $result;
  }
  
  private function getfileitem($id, $part) {
    $files = tfiles::instance();
    $item = $files->getitem($id);
    $args = targs::instance();
    $args->add($item);
    $args->idtag = "$part-$id";
    $args->part = $part;
    $args->id = $id;
    if ($item['media'] == 'image') {
      $img = '<img src="litepublisher::$options.files/files/$filename" title="$filename" />';
      if ($item['preview'] == 0) {
        $args->preview = '';
      } else {
        $preview = $files->getitem($item['preview']);
        $imgarg = new targs();
        $imgarg->add($preview);
        $theme = ttheme::instance();
        $args->preview =$theme->parsearg($img, $imgarg);
      }
      return $this->html->image($args);
    } else {
      return $this->html->fileitem($args);
    }
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
    $id = $parser->uploadfile($_FILES["Filedata"]["name"], $_FILES["Filedata"]["tmp_name"], '', false);
    $result = $this->getfileitem($id, 'curr');
    
    return "<?php
    @Header( 'Cache-Control: no-cache, must-revalidate');
    @Header( 'Pragma: no-cache');
    @header('Content-Type: text/html; charset=utf-8');
    @ header('Last-Modified: ' . date('r'));
    @header('X-Pingback: ". litepublisher::$options->url . "/rpc.xml');
    ?>" . $result;
  }
  
}//class
?>