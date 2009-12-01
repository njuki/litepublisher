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
$s = str_replace("\n\n", "\n", $s);
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

$s = str_replace("\r\n", "\n", $s);
$s = str_replace("\r", "\n", $s);
$s = str_replace("\n\n", "\n", $s);

$theme->menu = $this->parsemenu($this->parsetag($s, 'menu', '$template.menu'));
$theme->content = $this->parsecontent($this->parsetag($s, 'content', '$template.content'));
$theme->sitebars = $this->parsesitebars($s);
$theme->theme= $s;
}

private function parsemenu($s) {
$result = array();
$item = $this->parsetag($s, 'item', '%s');
if ($submenu = $this->parsetag($item, 'submenu', '%3$s')) $result['submenu'] = $submenu;
$result['item'] = $item;
$result['current'] = $this->parsetag($s, 'current', '');
$result[0] = $s;
//hover
if ($id = $this->getidtag('*', $s)) {
$result['id'] = $id;
preg_match('/\<(\w*?)\s/',$item, $t);
$result['tag'] = $t[1];
}
return $result;
}

private function parsecontent($s) {
$result = array();
$result['post']= $this->parsepost($this->parsetag($s, 'post', ''));
$result['excerpts'] = $this->parse_excerpts($this->parsetag($s, 'excerpts', ''), $result['post']);
$result['navi'] = $this->parsenavi($this->parsetag($s, 'navi', ''));
$result['admin'] = $this->parseadmin($this->parsetag($s, 'admin', ''));
$result['simple'] = $this->parsetag($s, 'simple', '');
$result['notfound'] = $this->parsetag($s, 'notfound', '');
$result['menu']= $this->parsetag($s, 'menu', '');
return $result;
}

private function parse_excerpts($s, array &$post) {
$result = array();
$result['excerpt'] = $this->parse_excerpt($this->parsetag($s, 'excerpt', '%s'), $post);
$result['lite'] = $this->parselite($this->parsetag($s, 'lite', ''));
$result[0] = $s;
return $result;
}

private function parselite($s) {
$result = array();
$result['excerpt'] = $this->parsetag($s, 'excerpt', '%s');
$result[0] = $s;
return $result;
}

private function parse_excerpt($s, array &$post) {
$result = array();
if ($commontags = $this->parsecommontags($s, 'commontags', '')) {
$result['commontags'] = $commontags;
}

if ($categories = $this->parsecommontags($s, 'categories', '$post.categorieslinks')) {
$result['categories'] = $categories;
} elseif ($commontags) {
$result['categories'] = $commontags;
$result['categories'][0] = str_replace('commontags', 'categories', $commontags[0]);
} else {
$result['categories'] = $post['categories'];
}

if ($tags = $this->parsecommontags($s, 'tags', '$post.tagslinks')) {
$result['tags'] = $tags;
} elseif ($commontags) {
$result['tags'] = $commontags;
$result['tags'][0] = str_replace('commontags', 'tags', $commontags[0]);
} else {
$result['tags'] = $post['tags'];
}

if ($dateformat = $this->parsetag($s, 'date', '$post.date')) {
$result['dateformat'] = $dateformat;
} else {
$result['dateformat'] = $post['dateformat'];
}

$result['more'] = $this->parsetag($s, 'more', '$post.morelink');
$result['previews'] = $this->parsepreviews($this->parsetag($s, 'previews', '$post.previews'));
$result[0] = $s;
return $result;
}

private function parsepreviews($s) {
$result = array();
$result['preview'] = $this->parsetag($s, 'preview', '%s');
$result[0] = $s;
return $result;
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

if ($categories = $this->parsecommontags($s, 'categories', '$post.categorieslinks')) {
$result['categories'] = $categories;
} else {
$result['categories'] = $commontags;
$result['categories'][0] = str_replace('commontags', 'categories', $commontags[0]);
}

if ($tags = $this->parsecommontags($s, 'tags', '$post.tagslinks')) {
$result['tags'] = $tags;
} else {
$result['tags'] = $commontags;
$result['tags'][0] = str_replace('commontags', 'tags', $commontags[0]);
}

$result['files'] = $this->parsefiles($this->parsetag($s, 'files', '$post.filelist'));
$result['more'] = $this->parsetag($s, 'more', '');
$result['rss'] = $this->parsetag($s, 'rss', '$post.rsscomments');
$result['prevnext']  = $this->parseprevnext($this->parsetag($s, 'prevnext', '$post.prevnext'));
$result['templatecomments'] = $this->parsetemplatecomments($this->parsetag($s, 'templatecomments', '$post.templatecomments'));
// после комментариев из за секции date в комментарии
$result['dateformat'] = $this->parsetag($s, 'date', '$post.date');
$result[0] = $s;
return $result;
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
$result = array();
$result['prev'] = $this->parsetag($s, 'prev', '%s');
$result['next'] = $this->parsetag($s, 'next', '');
$result['link'] = $this->parsetag($s, 'link', '');
$result['current'] = $this->parsetag($s, 'current', '');
$result['divider'] = $this->parsetag($s, 'divider', '');
$result[0] = $s;
return $result;
}

private function parseadmin($s) {
$result = array();
$result['area'] = trim($this->parsetag($s, 'area', ''));
$result['edit'] = trim($this->parsetag($s, 'edit', ''));
return $result;
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
$result['comment'] = $this->parsecomment($this->parsetag($s, 'comment', '%1$s'));
$result[0] = $s;
return $result;
}

private function parsecomment($s) {
$result = array();
    $result['class1'] = $this->parsetag($s, 'class1', '$class');
    $result['class2'] = $this->parsetag($s, 'class2', '');
    $result['hold'] = $this->parsetag($s, 'hold', '$hold');
$result['dateformat'] = $this->parsetag($s, 'date', '$comment.date');
$result[0] = $s;
return $result;
}
 
private function parsepingbacks($s) {
   $result = array();
    $result['pingback'] = $this->parsetag($s, 'pingback', '%1$s');
    $result[0] = $s;
return $result;
}

private function parsesitebars(&$s) {
$result = array();
while ($sitebar = $this->parsetag($s, 'sitebar', '$template.sitebar')) {
$result[] = $this->parsesitebar($sitebar);
}
return $result;
}

private function parsesitebar($s) {
$result = array();
$result['widget'][0] = $this->parsetag($s, 'widget', '');

foreach (array('categories', 'tags', 'archives', 'links', 'posts', 'comments', 'friends', 'meta') as $name) {
if ($content =$this->parsetag($s, $name, ''))  {
$widget = array();
if ($item = $this->parsetag($content, 'item', '%s')) {
$widget['item'] = $item;
} else {
$widget['item'] = $this->GetDefaultWidgetItem($name);
}
$widget[0] = $content;
$result[$name] = $widget;
}
}

$result[0] = $s;
return $result;
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

private function GetDefaultWidgetItem($name) {
switch ($name) {
case 'categories':
case  'tags':
return '<li><a href="%1$s" title="%2$s">%2$s</a>%3$s</li>';

case 'archives':
return '<li><a href="%1$s" rel="archives" title="%2$s">%2$s</a>%3$s</li>';

case 'post':
return '<li><strong><a href="$post->link" rel="bookmark" title="Permalink to $post->title">$post->iconlink$post->title</a></strong><br />     <small>$post->localdate</small></li>';

case 'comments':
return '<li><strong><a href=" $options->url$posturl#comment-$id" title="$name $onrecent $title">$name $onrecent $title</a></strong>: $s...</li>';

case 'link':
return '<li><a href="$url" title="$title">$text</a></li>';

default:
return '<li><a href="$url" title="$title">$text</a></li>';
}
}

}//class

?>