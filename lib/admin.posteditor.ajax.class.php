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
}

private static function error403() {
return '<?php header(\'HTTP/1.1 403 Forbidden\', true, 403); ?>' . turlmap::htmlheader(false) . 'Forbidden';
}

  public function request($arg) {
    if (!litepublisher::$options->cookieenabled) return self::error403();
      if (!litepublisher::$options->authcookie()) return self::error403();
    if (litepublisher::$options->group != 'admin') {
      $groups = tusergroups::instance();
      if (!$groups->hasright(litepublisher::$options->group, 'author')) return self::error403();
    }

    $this->idpost = tadminmenu::idget();
    if ($this->idpost > 0) {
      $posts = tposts::instance();
      if (!$posts->itemexists($this->idpost)) return self::error403();
    }
    $post = tpost::instance($this->idpost);
    if ((litepublisher::$options->group == 'author') && (litepublisher::$options->user != $post->author)) return self::error403();
return $this->getcontent();
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

case 'files':
    $args = targs::instance();
$args->ajax = tadminhtml::getadminlink('/admin/ajaxposteditor.htm', "id=$post->id&get");
    $files = tfiles::instance();
if (count($post->files) == 0) {
   $args->currentfiles = '<ul></ul>';
} else {
$theme = ttheme::instance();
$templates = $theme->content->post->filelist->array;
foreach ($templates as $name => $tml) {
$tml = str_replace('$id', 'curfile-$id', $tml);
$tml = str_replace('$post.id', 'curpost-$post.id', $tml);

$templates[$name] = str_replace(
'<li>',
 '<li><input type="checkbox" name="currentfile-$id" id="currentfile-$id" value="$id">',
$tml);
}

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
$theme = ttheme::instance();
$templates = $theme->content->post->filelist->array;
foreach ($templates as $name => $tml) {
$tml = str_replace('$id', 'filepage-$id', $tml);
$tml = str_replace('$post.id', 'post-$post.id', $tml);
$templates[$name] = str_replace(
'<li>',
 '<li><input type="checkbox" name="itemfilepage-$id" id="itemfilepage-$id" value="$id">',
$tml);
}
    $files = tfiles::instance();
$result = $files->getlist($list, $templates);
    $result .= $html->page($args);
break;

default:
$result = var_export($_GET, true);
}
return turlmap::htmlheader(false) . $result;
}

}//class
?>