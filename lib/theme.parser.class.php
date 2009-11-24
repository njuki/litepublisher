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
$this->theme = $theme;
$s = file_get_contents($filename);
$this->parsemenu($s);
$this->parsecontent($s);
$theme->sitebarscount = $this->parsesitebars($s);
$theme->main = $s;
}

private function parsemenu(&$s) {
$menu = &$this->theme->menu;
$menus = $this->parsetag($s, 'menu', '$template->menu');
$item = $this->parsetag($menus, 'item', '%s');
if ($submenu = $this->parsetag($item, 'submenu', '%3$s')) $menu['submenu'] = $submenu;
$menu['item'] = $item;
$menu['current'] = $this->parsetag($menus, 'current', '');
$menu['menu'] = $menus;
//hover
if ($id = $this->getidtag('*', $menus)) {
$menu['id'] = $id;
preg_match('/\<(\w*?)\s/',$item, $t);
$menu['tag'] = $t[1];
}
}

private function parsecontent(&$s) {
$theme = $this->theme;
$content = $this->parsetag($s, 'content', '$template->content');
$this->parse_excerpts($this->parsetag($content, 'excerpts', ''));
$lite = $this->parsetag($content, 'lite', '');
$theme->excerpts['lite_excerpt'] = $this->parsetag($lite, 'excerpt', '%s');
$theme->excerpts['lite'] = $lite;

$this->parsepost($this->parsetag($content, 'post', ''));

$theme->menucontent = $this->parsetag($content, 'menu', '');
$theme->simplecontent = $this->parsetag($content, 'simplecontent', '');
if ($theme->simplecontent == '') $theme->simplecontent  = '%s';
$theme->nocontent = $this->parsetag($content, 'nocontent', '');
if ($theme->nocontent == '') $theme->nocontent  = '$lang->nocontent';
$this->parsenavi($this->parsetag($content, 'navi', ''));
$this->parseadmin($this->parsetag($content, 'admin', ''));
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

private function parsepost($s) {
$post = &$this->theme->post;

$categories = $this->parsetag($s, 'categories', '$post->categorieslinks'); 
$post['category'] = $this->parsetag($categories, 'category', '%s');
$post['categoriesdivider'] = $this->parsetag($categories, 'divider', '');
$post['categories'] = $categories;

$tags = $this->parsetag($s, 'tags', '$post->tagslinks'); 
$post['tag'] = $this->parsetag($tags, 'tag', '%s');
$post['tagsdivider'] = $this->parsetag($tags, 'divider', '');
$post['tags'] = $tags;

$post['more'] = $this->parsetag($s, 'more', '');
$this->parsefiles($this->parsetag($s, 'files', '$post->filelist'));

$post['rss'] = $this->parsetag($s, 'rss', '$post->rsscomments');

$prevnext = $this->parsetag($s, 'prevnext', '$post->prevnext');
$post['prev'] = $this->parsetag($prevnext, 'prev', '%s');
$post['next'] = $this->parsetag($prevnext, 'next', '');
$post['prevnext'] = $prevnext;

$this->parsecomments($this->parsetag($s, 'templatecomments', '$post->templatecomments'));

$post['tml'] = $s;
}

private function parsefiles($s) {
$files = &$this->theme->files;
$files['file'] = $this->parsetag($s, 'file', '%s');
$files['image'] = $this->parsetag($s, 'image', '');
$files['video'] = $this->parsetag($s, 'video', '');
$files['files'] = $s;
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

private function parsecomments($s) {
$theme = $this->theme;
    $comments = $this->parsetag($s, 'comments', '');
    $theme->comments['count'] = $this->parsetag($comments, 'count', '');
    $comment = $this->parsetag($comments, 'comment', '%1$s');
    $theme->comments['comments'] = $comments;
    $theme->comments['class1'] = $this->parsetag($comment, 'class1', '$class');
    $theme->comments['class2'] = $this->parsetag($comment, 'class2', '');
    $theme->comments['hold'] = $this->parsetag($comment, 'hold', '$hold');
    $theme->comments['comment'] = $comment;
    
    $pingbacks = $this->parsetag($s, 'pingbacks', '');
    $theme->comments['pingback'] = $this->parsetag($pingbacks, 'pingback', '%1$s');
    $theme->comments['pingbacks'] = $pingbacks;
    
    $theme->comments['closed'] = $this->parsetag($s, 'closed', '');
$theme->comments['form'] = $this->parsetag($s, 'form', '');
$theme->comments['confirmform'] = $this->parsetag($s, 'confirmform', '');
if (empty($theme->comments['confirmform'])) {
$theme->comments['confirmform'] = '<h2>$lang->formhead</h2>
<form name="preform" method="post" action="">
  <p><input type="submit" name="submit" value="$lang->robot"/></p>
</form>

<form name="form" method="post" action="">
<input type="hidden" name="confirmid" value="$confirmid" />
  <p><input type="submit" name="submit" value="$lang->human"/></p>
</form>';
 }
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
$theme->widgets['comment'] = '<li><strong><a href=" $options->url$posturl#comment-$id" title="$name $onrecent $title">$name $onrecent $title</a></strong>: $content...</li>';
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