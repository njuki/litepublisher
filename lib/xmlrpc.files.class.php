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
$lang = tlocal::instance('files');
}
  
  public function delete($login, $password, $id) {
    $this->auth($login, $password, 'editor');
$files = tfiles::instance();
    if (!$files->delete((int) $id)) return $this->xerror(404, "File not deleted");
    return true;
  }

private function getpagelinks($current, $count) {

$links = array();
for ($i = 1; $i <= $count; $i++) {
if ($i == $current) {
$list[] = "$i";
} else {
$list[] = "<a onclick='post.getpage($i);'>$i</a>";
}
}
$result = $this->html->pagelinks();
return sprintf($result, implode(' | ', $list));
}

private function get_page($index) {
$result = '';
$files = tfiles::instance();
    $perpage = 20;
    if (dbversion) {
      $sql = 'parent =0';
      $sql .= $options->user <= 1 ? '' : " and author = $options->user";
      $count = $files->db->getcount($sql);
    } else {
      $list= array();
      foreach ($files->items as $id => $item) {
        if ($item['parent'] != 0) continue;
        if ($options->user > 1 && $options->user != $item['author']) continue;
        $list[] = $id;
      }
      $count = count($list);
    }
    
    $from = max(0, $count - $index * $perpage);
    
    if (dbversion) {
      $items = $files->db->getitems($sql . " limit $from, $perpage");
foreach ($items as $item) $files->items[$item['id']] = $item;
    } else {
      $list = array_slice($list, $from, $perpage);
    }
    
    $result .= sprintf($this->html->h2->countfiles, $count, $from, $from + count($items));
$result .= $this->getpagelinks($index, ceil($count / $perpage));
$page = '';
    $args = targs::instance();
    foreach ($list as $id) {
      $page = $this->getfileitem($id);
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

private function getpostfiles($idpost) {
$result = '';
$post = tpost::instance((int) $idpost);
foreach ($post->files as $id) {
$result .= $this->getfileitem($id);
}
return $result;
} 

private function getfileitem($id) {
$files = tfiles::instance();
$item = $files->getitem($id);

$args = targs::instance();
      $args->add($item);
if ($item['media'] == 'image') {
    $img = '<img src="$options.files/files/$filename" title="$filename" />';
      if ($item['preview'] == 0) {
        $args->preview = '';
      } else {
        $preview = $this->getitem($item['preview']);
        $imgarg = new targs();
        $imgarg->add($preview);
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
global $options;
if (empty($_POST['admincookie'])) return false;
$_COOKIE['admin'] = $_POST['admincookie'];
    $options->admincookie = $options->cookieenabled && $options->authcookie();
if (!$options->admincookie) return false;
      if (($options->group == 'admin') || ($options->group == 'editor')) return true;
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

if (!$this->postauth()) return $this->error500('Unauthorized');

	if (!isset($_FILES["Filedata"]) || !is_uploaded_file($_FILES["Filedata"]["tmp_name"]) || $_FILES["Filedata"]["error"] != 0) return $this->error500('Something wrong in post data');

      $parser = tmediaparser::instance();
      $id = $parser->uploadfile($_FILES["Filedata"]["name"], $_FILES["Filedata"]["tmp_name"], '', false);
$result = $this->getfileitem($id);

return "<?php
    @Header( 'Cache-Control: no-cache, must-revalidate');
    @Header( 'Pragma: no-cache');
        @header('Content-Type: text/html; charset=utf-8');
    @ header('Last-Modified: ' . date('r'));
    @header('X-Pingback: $options->url/rpc.xml');
?>" . $result;
}

}//class
?>