<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class tthemeparser {
public $theme;
  private $abouts;

public static function instance() {
return getinstance(__class__);
}

  public function parsetag(&$s, $tag, $replace) {
    $result = '';
    $opentag = "<!--$tag-->";
    $closetag = "<!--/$tag-->";
    if(is_int($i = strpos($s, $opentag)) && ($j = strpos($s, $closetag))) {
      $result = substr($s, $i + strlen($opentag), $j - $i - strlen($opentag));
      $s = substr_replace($s, $replace, $i, $j - $i + strlen($closetag));
    }
    return $result;
  }

public function getidtag($tag, $s) {
if (preg_match("/<$tag\\s*.*?id\\s*=\\s*['\"]([^\"'>]*)/i", $s, $m)) {
return $m[1];
}
return false;
}
  
public function parse($filename, $theme) {
$s = file_get_contents($filename);
$theme->menu = $this->parsemenu($this->parsetag($s, 'menu', '$template->menu'));
$theme->content = $this->parsecontent($this->parsetag($s, 'content', '$template->content'));
$theme->sitebars = $this->parsesitebars($s);
$theme->theme[0]= $s;
}

private function parsemenu($s) {
$menu = &$this->theme->data['menu'];
$item = $this->parsetag($s, 'item', '%s');
if ($submenu = $this->parsetag($item, 'submenu', '%3$s')) $menu['submenu'] = $submenu;
$menu['item'] = $item;
$menu['current'] = $this->parsetag($s, 'current', '');
$menu[0] = $s;
//hover
if ($id = $this->getidtag('*', $s)) {
$menu['id'] = $id;
preg_match('/\<(\w*?)\s/',$item, $t);
$menu['tag'] = $t[1];
}

}

private function parsecontent($s) {
$result = array();
$result['post']= $this->parsepost($this->parsetag($s, 'post', ''));
$result['navi'] = $this->parsenavi($this->parsetag($s, 'navi', ''));
$result['admin'] = $this->parseadmin($this->parsetag($s, 'admin', ''));
$result['excerpts'] = $this->parse_excerpts($this->parsetag($s, 'excerpts', ''));

$lite = $this->parsetag($s, 'lite', '');
$theme->excerpts['lite_excerpt'] = $this->parsetag($lite, 'excerpt', '%s');
$theme->excerpts['lite'] = $lite;

$result['menu']= $this->parsetag($s, 'menu', '');
$result['simple'] = $this->parsetag($s, 'simple', '');
$result['notfound'] = $this->parsetag($s, 'notfound', '');
return $result;
}

private function parse_excerpts($s) {
$excerpts = &$this->theme->excerpts;
$theme = $this->theme;
$excerpt = $this->parsetag($s, 'excerpt', '%s');

$categories = $this->parsetag($excerpt, 'categories', '$post->excerptcategories'); 
$excerpts['category'] = $this->parsetag($categories, 'category', '%s');
$excerpts['categorydivider'] = $this->parsetag($categories, 'divider', '');
$excerpts['categories'] = $categories;

$tags = $this->parsetag($excerpt, 'tags', '$post->excerpttags'); 
$excerpts['tag'] = $this->parsetag($tags, 'tag', '%s');
$excerpts['tagdivider'] = $this->parsetag($tags, 'divider', '');
$excerpts['tags'] = $tags;

$excerpts['more'] = $this->parsetag($excerpt, 'more', '$post->morelink');
$screenshots = $this->parsetag($excerpt, 'screenshots', '$post->screenshots');
$theme->files['screenshot'] = $this->parsetag($screenshots, 'screenshot', '%s');
$theme->files['screenshots'] = $screenshots;

$excerpts['excerpt'] = $excerpt;
$theme->excerpts['normal'] = $s;
}

private function parsecommontags(&$s, $name, $replace) {
if ($commontags = $this->parsetag($s, $name, $replace)) {
$result = array();
$result['item'] = $this->parsetag($commontags, 'item', '%s');
$result['divider'] = $this->parsetag($commontags, 'divider', '');
$result[0] = $commontags;
return $result;
}
return false;
}

private function parsepost($s) {
$result = array();
if ($commontags = $this->parsecommontags($s, 'commontags', '')) {
$result['commontags'] = $commontags;
}

if ($categories = $this->parsecommontags($s, 'categories', '$post->categorieslinks')) {
$result['categories'] = $categories;
} else {
$result['categories'] = $commontags;
$result['categories'][0] = str_replace('commontags', 'categories', $commontags[0]);
}

if ($tags = $this->parsecommontags($s, 'tags', '$post->tagslinks')) {
$result['tags'] = $tags;
} else {
$result['tags'] = $commontags;
$result['tags'][0] = str_replace('commontags', 'tags', $commontags[0]);
}

$result['files'] = $this->parsefiles($this->parsetag($s, 'files', '$post->filelist'));
$result['more'] = $this->parsetag($s, 'more', '');
$result['rss'] = $this->parsetag($s, 'rss', '$post->rsscomments');
$result['prevnext']  = $this->parseprevnext($this->parsetag($s, 'prevnext', '$post->prevnext'));
$result['comments'] = $this->parsetemplatecomments($this->parsetag($s, 'templatecomments', '$post->templatecomments'));
$result[0] = $s;
echo "<pre>\n";
var_dump($result);
exit();
return $s;
}

private function parsefiles($s) {
$result = array();
$result['file'] = $this->parsetag($s, 'file', '%s');
$result['image'] = $this->parsetag($s, 'image', '');
$result['video'] = $this->parsetag($s, 'video', '');
$result[0] = $s;
return $result;
}

private function parseprevnext($s) {
$result = array();
$result['prev'] = $this->parsetag($s, 'prev', '%s');
$result['next'] = $this->parsetag($s, 'next', '');
$result[0] = $s; return $result;
}

private function parsenavi($s) {
$theme = $this->theme;
$theme->navi['prev'] = $this->parsetag($s, 'prev', '%s');
$theme->navi['next'] = $this->parsetag($s, 'next', '');
$theme->navi['link'] = $this->parsetag($s, 'link', '');
$theme->navi['current'] = $this->parsetag($s, 'current', '');
$theme->navi['divider'] = $this->parsetag($s, 'divider', '');
$theme->navi['navi'] = $s;
}

private function parseadmin($s) {
$theme = $this->theme;
$theme->admin['area'] = trim($this->parsetag($s, 'area', ''));
$theme->admin['edit'] = trim($this->parsetag($s, 'edit', ''));
}

private function parsetemplatecomments($s) {
$result = array();
    $result['comments'] = $this->parsecomments($this->parsetag($s, 'comments', ''));
    $result['pingbacks'] = $this->parsepingbacks($this->parsetag($s, 'pingbacks', ''));
    $result['closed'] = $this->parsetag($s, 'closed', '');
$result['form'] = $this->parsetag($s, 'form', '');
$result['confirmform'] = $this->parsetag($s, 'confirmform', '');
if ($result['confirmform'] == '') $result['confirmform'] = $this->getdefaultconfirmform();
return $result;
}

private function parsecomments($s) {
$result = array();
    $result['count'] = $this->parsetag($s, 'count', '');
    $comment = $this->parsetag($s, 'comment', '%1$s');
    $result['class1'] = $this->parsetag($comment, 'class1', '$class');
    $result['class2'] = $this->parsetag($comment, 'class2', '');
    $result['hold'] = $this->parsetag($comment, 'hold', '$hold');
    $result['comment'] = $comment;
$result[0] = $s;
return $result;
}
 
private function parsepingbacks($s) {
   $result = array();
    $result['pingback'] = $this->parsetag($s, 'pingback', '%1$s');
    $result[0] = $s;
return $result;
}

private function getdefaultconfirmform() {
return '<h2>$lang->formhead</h2>
<form name="preform" method="post" action="">
  <p><input type="submit" name="submit" value="$lang->robot"/></p>
</form>

<form name="form" method="post" action="">
<input type="hidden" name="confirmid" value="$confirmid" />
  <p><input type="submit" name="submit" value="$lang->human"/></p>
</form>';
 }

private function parsesitebars(&$s) {
$theme = $this->theme;
$index = 0;
while ($sitebar = $this->parsetag($s, 'sitebar', '$template->sitebar')) {
$theme->widgets[$index] = array();
$widgets = &$theme->widgets[$index];
$widgets['widget'] = $this->parsetag($sitebar, 'widget', '');

if ($categories =$this->parsetag($sitebar, 'categories', ''))  {
if ($item = $this->parsetag($categories, 'item', '%s')) {
$theme->widgets['tag'] = $item;
}
$widgets['categories'] = $categories;
}

if (empty($theme->widgets['tag'])) {
$theme->widgets['tag'] = '<li><a href="%1$s" title="%2$s">%2$s</a>%3$s</li>';
}

if ($archives =$this->parsetag($sitebar, 'archives', '')) $widgets['archives'] = $archives;
if ($links =$this->parsetag($sitebar, 'links', '')) $widgets['links'] = $links;

if ($posts =$this->parsetag($sitebar, 'posts', '')) {
if ($item = $this->parsetag($posts, 'item', '%s')) {
$theme->widgets['post'] = $item;
}
$widgets['posts'] = $posts;
}

if (empty($theme->widgets['post'])) {
$theme->widgets['post'] = '<li><strong><a href="$post->link" rel="bookmark" title="Permalink to $post->title">$post->iconlink$post->title</a></strong><br />
    <small>$post->localdate</small></li>';
}

if ($comments =$this->parsetag($sitebar, 'comments', '')) {
if ($item = $this->parsetag($comments, 'item', '%s')) {
$theme->widgets['comment'] = $item;
}
$widgets['comments'] = $comments;
}

if (empty($theme->widgets['comment'])) {
$theme->widgets['comment'] = '<li><strong><a href=" $options->url$posturl#comment-$id" title="$name $onrecent $title">$name $onrecent $title</a></strong>: $s...</li>';
}

if ($links =$this->parsetag($sitebar, 'links', '')) {
if ($item = $this->parsetag($links, 'item', '%s')) {
$theme->widgets['link'] = $item;
}
$widgets['links'] = $links;
}

if (empty($theme->widgets['link'])) {
$theme->widgets['link'] = '<li><a href="$url" title="$title">$text</a></li>';
}

if ($meta =$this->parsetag($sitebar, 'meta', '')) $widgets['meta'] = $meta;
$index++;
}
return $index;
}

//manager
  public function getabout($name) {
    global $paths, $options;
    if (!isset($this->abouts)) $this->abouts = array();
    if (!isset($this->abouts[$name])) {
$about = parse_ini_file($paths['themes'] . $name . DIRECTORY_SEPARATOR . 'about.ini', true);
//слить языковую локаль в описание
if (isset($about[$options->language])) {
$about['about'] = $about[$options->language] + $about['about'];
}
$this->abouts[$name] = $about['about'];
    }
    return $this->abouts[$name];
  }
  
public function changetheme($old, $name) {
    global $paths, $options;
$template = ttemplate::instance();

      if ($about = $this->getabout($old)) {
        if (!empty($about['about']['pluginclassname'])) {
          $plugins = tplugins::instance();
          $plugins->delete($old);
        }
      }

      $template->data['theme'] = $name;
      $template->path = $paths['themes'] . $name . DIRECTORY_SEPARATOR  ;
      $template->url = $options->url  . '/themes/'. $template->theme;

$theme = ttheme::instance();
$this->parse($template->path . 'index.tml', $theme);
$theme->basename = 'themes' . DIRECTORY_SEPARATOR . "$template->theme-$template->tml";
$theme->save();
$template->save();

      $about = $this->getabout($name);
      if (!empty($about['about']['pluginclassname'])) {
        $plugins = tplugins::instance();
        $plugins->addext($name, $about['about']['pluginclassname'], $about['about']['pluginfilename']);
      }

      $urlmap = turlmap::instance();
      $urlmap->clearcache();
  }
  
}//class

?>