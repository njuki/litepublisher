<?php

class tthemeparser {
public $theme;
  private $abouts;

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
}

private function parsemenu(&$s) {
$menu = &$this->theme->menu;
$menus = $this->parsetag($s, 'menu, '$template->menu');
$item = $this->parsetag($menus, 'item', '%s');
if ($submenu = $this->parsetag($item, 'submenu', '%3$s')) $menu['submenu'] = $submenu;
$menu['item'] = $item;
$menu['current'] = $this->parsetag($menus, 'current', '');
$menu['menu'] = $menus;
//hover
if (preg_match('/<\w*?\s*.*?id\s*=\s*[\'"]([^"\'>]*)/i', 
if ($id = $this->getidtag('*', menus)) {
$menu['id'] = $id;
preg_match('/\<(\w*?)\s/',$item, $t);
$menu['tag'] = $t[1];
}
}

private function parsecontent(&$s) {
$theme = $this->theme;
$ content = $this->parsetag($s, 'content', '$template->content');

$excerpt = $this->parsetag($content, 'excerpt', '');
$theme->more['link'] = $this->parsetag($excerpt, 'morelink', '$post->morelink');
$screenshots = $this->parsetag($excerpt= , 'screenshots', '$post->screenshots);
$theme->files['screenshot'] = $this->parsetag($screenshots, 'screenshot', '%s');
$theme->files['screenshots'] = $screenshots;
$theme->excerpt = $excerpt;

$post = $this->parsetag($content, 'post', '');
$theme->more['anchor'] = $this->parsetag($post, 'moreanchor', '');
$files = $this->parsetag($post, 'files', '$post->filelist');
$theme->files['file'] = $this->parsetag$files, 'file', '%s');
$theme->files['image'] = $this->parsetag($files, 'image', '');
$theme->files['video'] = $this->parsetag($files, 'video', '');
$theme->files['files'] = $files;
$comments = $this->parsetag($post, 'templatecomments', '$post->templatecomments');
$this->parsecomments($comments);
$theme->post = $post;

$theme->menucontent = $this->parsetag($content, 'menucontent', '');
$theme->simplecontent = $this->parsetag($content, 'simplecontent', '');
if (theme->simplecontent == '') theme->simplecontent  = '%s';

$this->parsenavi($this->parsetag($content, 'navi', ''));
$this->parseadmin($this->parsetag($content, 'admin', '');
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
<input type=hidden name="confirmid" value="$confirmid" />
  <p><input type="submit" name="submit" value="$lang->human"/></p>
</form>';
{
  }

private function parse sitebars(&$s) {
$theme = $this->theme;
$index = 0;
while ($sitebar = $this->parsetag($s, 'sitebar', '$template->sitebar')) {
$theme->widgets[$index] = array();
$widgets = &$theme->widgets[$index];
$widgets['widget'] = $this->parsetag($sitebar, 'widget', '');

if ($categories =$this->parsetag($sitebar, 'categories', ''))  {
if ($item = $this->parsetag($categories, 'item', '%s')) 
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
//����� �������� ������ � ��������
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
      $urlmap->clearcache);
  }
  
}//class

?>