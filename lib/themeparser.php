<?php

class tthemeparser {
public $theme;

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

}

private function parsecontent(&$s) {
$theme = $this->theme;
$ content = $this->parsetag($s, 'content', '$template->content');
$theme->excerpt = str_replace('"', '\"', $this->parsetag($content, 'excerpt', ''));
$post = $this->parsetag($content, 'post', '');
$comments = $this->parsetag($post, 'templatecomments', '$post->templatecomments');
$this->parsecomments($comments);
$theme->post = str_replace('"', '\"', $post);

$theme->menuitem = str_replace('"', '\"', $this->parsetag($content, 'menuitem', ''));

$navi = $this->parsetag($content, 'navi', '');
$theme->navi['prev'] = $this->parsetag($navi, 'prev', '%s');
$theme->navi['next'] = $this->parsetag($navi, 'next', '');
$theme->navi['link'] = $this->parsetag($navi, 'link', '');
$theme->navi['current'] = $this->parsetag($navi, 'current', '');
$theme->navi['navi'] = $navi;
}

private function parsecomments($s) {
$theme = $this->theme;
    $comments = $this->parsetag($s, 'comments', '');
    $count= $this->parsetag($comments, 'count', '');
    $theme->comments['count'] = str_replace('"', '\"', ltrim($count));
    
    $comment = $this->parsetag($comments, 'comment', '%1$s');
    $theme->comments['comments'] = $comments;
    $theme->comments['class1'] = $this->parsetag($comment, 'class1', '$class');
    $theme->comments['class2'] = $this->parsetag($comment, 'class2', '');
    $theme->comments['hold'] = $this->parsetag($comment, 'hold', '$hold');
    $theme->comments['comment'] = str_replace('"', '\"', ltrim($comment));
    
    $pingbacks = str_replace('"', '\"', $this->parsetag($s, 'pingbacks', ''));
    $theme->comments['pingback'] = $this->parsetag($pingbacks, 'pingback', '%1$s');
    $theme->comments['pingbacks'] = $pingbacks;
    
    $theme->comments['closed'] = str_replace('"', '\"', $this->parsetag($s, 'closed', ''));
$theme->commentform = $this->parsetag($s, 'form', '');
  }

private function parse sitebars(&$s) {
$theme = $this->theme;
$index = 0;
while ($sitebar = $this->parsetag($s, 'sitebar', '$template->sitebar')) {
$theme->widgets[$index] = array();
$widgets = &$theme->widgets[$index];
$widgets['widget'] = $this->parsetag($sitebar, 'widget', '');
if ($categories =$this->parsetag($sitebar, 'categories', '')) $widgets['categories'] = $categories;
if ($archives =$this->parsetag($sitebar, 'archives', '')) $widgets['archives'] = $archives;
if ($links =$this->parsetag($sitebar, 'links', '')) $widgets['links'] = $links;
if ($posts =$this->parsetag($sitebar, 'posts', '')) {
if ($item = $this->parsetag($posts, 'item', '%s')) $widgets['post'] = str_replace('"', '\"', $item);
$widgets['posts'] = $posts;
}
if ($comments =$this->parsetag($sitebar, 'comments', '')) {
if ($item = $this->parsetag($comments, 'item', '%s')) $widgets['comment'] = $item;
$widgets['comments'] = $comments;
}
if ($meta =$this->parsetag($sitebar, 'meta', '')) $widgets['meta'] = $meta;
$index++;
}
return $index;
}

}//class

?>