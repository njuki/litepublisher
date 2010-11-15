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
   $html = THtmlResource ::instance();
    $html->section = 'editor';
$lang = tlocal::instance('editor');
    $post = tpost::instance($this->idpost);

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

}
return turlmap::htmlheader(false) . $result;
}

}//class
?>