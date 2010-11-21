<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tajaxposteditor  extends tevents {
  public $idpost;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
$this->basename = 'ajaxposteditor';
$this->addevents('onhead', 'oneditor');
$this->data['head'] = '';
$this->data['visual'] = '';
//'/plugins/tiny_mce/init.js';
//'/plugins/ckeditor/init.js';
$this->data['ajaxvisual'] = true;
}

public function dogethead($head) {
    $template = ttemplate::instance();
    $template->ltoptions[] = sprintf('visual: "%s"', $this->visual);
$head .= $this->head;
if (!$this->ajaxvisual && $this->visual) $head .= $template->getjavascript($this->visual);
$this->callevent('onhead', array(&$head));
return $head;
}

protected static function error403() {
return '<?php header(\'HTTP/1.1 403 Forbidden\', true, 403); ?>' . turlmap::htmlheader(false) . 'Forbidden';
}

public static function auth() {
    if (!litepublisher::$options->cookieenabled) return self::error403();
      if (!litepublisher::$options->authcookie()) return self::error403();
    if (litepublisher::$options->group != 'admin') {
      $groups = tusergroups::instance();
      if (!$groups->hasright(litepublisher::$options->group, 'author')) return self::error403();
    }
}

  public function request($arg) {
if (isset($_GET['upload'])) {
    if (empty($_POST['admincookie'])) return self::error403();
    if ( 'POST' != $_SERVER['REQUEST_METHOD'] ) {
      return "<?php
      header('Allow: POST');
      header('HTTP/1.1 405 Method Not Allowed', true, 405);
      header('Content-Type: text/plain');
      ?>";
}
    $_COOKIE['admin'] = $_POST['admincookie'];
}

if ($err = self::auth()) return $err;
    $this->idpost = tadminhtml::idparam();
    if ($this->idpost > 0) {
      $posts = tposts::instance();
      if (!$posts->itemexists($this->idpost)) return self::error403();
    }
    $post = tpost::instance($this->idpost);
    if ((litepublisher::$options->group == 'author') && (litepublisher::$options->user != $post->author)) return self::error403();
return $this->getcontent();
  }

private function getfiletemplates($id, $idpost, $li_id) {
$theme = ttheme::instance();
$result = $theme->content->post->filelist->array;
$replace = array(
'$id'=> $id,
'$post.id'=> $idpost,
'<li>' => sprintf('<li><input type="checkbox" name="%1$s" id="%1$s" value="$id">', $li_id)
);
foreach ($result as $name => $tml) {
$result[$name] = strtr($tml, $replace);
}
return $result;
}

public function getcontent() {
$theme = tview::instance(tviews::instance()->defaults['admin'])->theme;
   $html = tadminhtml ::instance();
    $html->section = 'editor';
$lang = tlocal::instance('editor');
    $post = tpost::instance($this->idpost);
ttheme::$vars['post'] = $post;

switch ($_GET['get']) {
case 'tags':
$result = $html->getedit('tags', $post->tagnames, $lang->tags);
    $items = array();
    $tags = ttags::instance();
    if ($tags->dbversion) $tags->select("", "order by itemscount");
    foreach ($tags->items as $id => $item) {
      $items[] = '<a onclick="tagtopost(this);">' . $item['title'] . "</a>";
    }
$result .= implode(",\n", $items);
break;

case 'posted':
$args = targs::instance();
      $args->date = $post->posted != 0 ?date('d.m.Y', $post->posted) : '';
      $args->time  = $post->posted != 0 ?date('H:i', $post->posted) : '';
$result = $html->datepicker($args);
break;

case 'status':
$form = new tautoform($post, 'editor', 'editor');
$form->add($form->commentsenabled, $form->pingenabled, 
$form->status('combo', array(
'published' => $lang->published, 
'draft' => $lang->draft
)));
$result = $form->getcontent();
break;

case 'view':
$result = $this->getviewicon($post->idview, $post->icon);
break;

case 'seo':
$form = new tautoform($post, 'editor', 'editor');
$form->add($form->url, $form->title2, $form->keywords, $form->description);
$result = $form->getcontent();
break;

case 'files':
    $args = targs::instance();
$args->ajax = tadminhtml::getadminlink('/admin/ajaxposteditor.htm', "id=$post->id&get");
    $files = tfiles::instance();
if (count($post->files) == 0) {
   $args->currentfiles = '<ul></ul>';
} else {
$templates = $this->getfiletemplates('curfile-$id', 'curpost-$post.id', 'currentfile-$id');
   $args->currentfiles = $files->getlist($post->files, $templates);
}

    if (dbversion) {
      $sql = "parent =0 and media <> 'icon'";
      $sql .= litepublisher::$options->user <= 1 ? '' : ' and author = ' . litepublisher::$options->user;
      $count = $files->db->getcount($sql);
    } else {
      $list= array();
$uid = litepublisher::$options->user;
      foreach ($files->items as $id => $item) {
        if (($item['parent'] != 0) || ($item['media'] == 'icon')) continue;
        if ($uid > 1 && $uid != $item['author']) continue;
        $list[] = $id;
      }
      $count = count($list);
    }

$pages = '';
    $perpage = 10;
$count = ceil($count/$perpage);
for ($i =1; $i <= $count; $i++) {
$args->index = $i;
$pages .= $html->pageindex($args);
}

    $args->pages = $pages;
$args->files = implode(',', $post->files);
    $result = $html->browser($args);
break;

case 'filepage':
$page = tadminhtml::getparam('page', 1);
$page = min(1, $page);
    $perpage = 10;
    $files = tfiles::instance();
    if (dbversion) {
      $sql = "parent =0 and media <> 'icon'";
      $sql .= litepublisher::$options->user <= 1 ? '' : ' and author = ' . litepublisher::$options->user;
      $count = $files->db->getcount($sql);
$pagescount = ceil($count/$perpage);
$page = min($page, $pagescount);
    $from = ($page -1)  * $perpage;
      $list = $files->select($sql, " order by posted desc limit $from, $perpage");
      if (!$list) $list = array();
    } else {
      $list= array();
$uid = litepublisher::$options->user;
      foreach ($files->items as $id => $item) {
        if (($item['parent'] != 0) || ($item['media'] == 'icon')) continue;
        if ($uid > 1 && $uid != $item['author']) continue;
        $list[] = $id;
      }
      $count = count($list);
$pagescount = ceil($count/$perpage);
$page = min($page, $pagescount);
    $from = ($page -1)  * $perpage;
      $list = array_slice($list, $from, $perpage);
    }

if (count($list) == 0) return '';

    $args = targs::instance();
$args->ajax = tadminhtml::getadminlink('/admin/ajaxposteditor.htm', "id=$post->id&get");
$args->page = $page;
$templates = $this->getfiletemplates('pagefile-$id', 'pagepost-$post.id', 'itemfilepage-$id');
    $files = tfiles::instance();
$result = $files->getlist($list, $templates);
    $result .= $html->page($args);
break;

case 'upload':
    if (!isset($_FILES["Filedata"]) || !is_uploaded_file($_FILES["Filedata"]["tmp_name"]) ||
 $_FILES["Filedata"]["error"] != 0) return self::error403();

    $parser = tmediaparser::instance();
    $id = $parser->uploadfile($_FILES["Filedata"]["name"], $_FILES["Filedata"]["tmp_name"], '', '', '', false);
$templates = $this->getfiletemplates('uploaded-$id', 'new-post-$post.id', 'newfile-$id');
$files = tfiles::instance();
    $result = $files->getlist(array($id), $templates);
break;

case 'contenttabs':
    $args = targs::instance();
$args->ajax = tadminhtml::getadminlink('/admin/ajaxposteditor.htm', "id=$post->id&get");
$result = $html->contenttabs($args);
break;

case 'excerpt':
$result = $this->geteditor('excerpt', $post->excerpt);
break;

case 'rss':
$result = $this->geteditor('rss', $post->rss);
break;

case 'more':
$result = $html->getedit('more', $post->moretitle, $lang->more);
break;

case 'filtered':
$result = $this->geteditor('filtered', $post->filtered);
break;

case 'upd':
$result = $this->geteditor('upd', '');
break;

default:
$result = var_export($_GET, true);
}
return turlmap::htmlheader(false) . $result;
}

public function geteditor($name, $value) {
   $html = tadminhtml ::instance();
    $html->section = 'editor';
$lang = tlocal::instance('editor');
$title = $lang->$name;
if ($name == 'raw') {
if ($this->ajaxvisual && $this->visual) $title .= $html->loadvisual();
if (__class__ == get_class($this)) $title .= $html->loadcontenttabs();
}
$theme = ttheme::instance();
return strtr($theme->content->admin->editor, array(
'$lang.$name' => $title,
'$name' => $name,
'$value' => tadminhtml::specchars($value)
));
}

public function getviewicon($idview, $icon) {
$result = tadminviews::getcomboview($idview);
if ($icons = tadminicons::getradio($icon)) {
$result .= $html->h2->icons;
$result .= $icons;
}
return $result;
}

}//class
?>