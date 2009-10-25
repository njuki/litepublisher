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
  
public function parse($filename) {
$this->theme = ttheme::instance();
$theme = $this->theme;
$s = file_get_contents($filename);
$this->parsemenu($s);
$theme->sitebarscount = $this->parsesitebars($s);
$theme->save();
}


private function parsemenu(&$s) {
$menu = &$this->theme->menu;
$menus = $this->parsetml($s, 'menu, '$template->menu');
$item = $this->parsetml($menus, 'item', '%s');
if ($submenu = $this->parsetml($item, 'submenu', '%3$s')) $menu['submenu'] = $submenu;
$menu['item'] = $item;
$menu['current'] = $this->parsetml($menus, 'current', '');
$menu['menu'] = $menus;
//hover

}

private function parsecontent(&$s) {
$content = &$this->theme->content;
$ str = $this->parsetml($s, 'content', '$template->content');
$content['post'] = $this->parsetml($str, 'post', '');

$str2= $this->parsetml($str, 'navi', '');
$content['navi'] = array();
$content['navi']['prev'] = $this->parsetml($str2, 'prev', '%s');
$content['navi']['next'] = $this->parsetml($str2, 'next', '');
$content['navi']['link'] = $this->parsetml($str2, 'link', '');
$content['navi']['current'] = $this->parsetml($str2, 'current', '');
$content['navi']['navi'] = $str2;
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
while ($sitebar = $this->parsetml($s, 'sitebar', '$template->sitebar')) {
$theme->widgets[$index] = array();
$widgets = &$theme->widgets[$index];
$widgets['widget'] = $this->parsetml($sitebar, 'widget', '');
if ($categories =$this->parsetml($sitebar, 'categories', '')) $widgets['categories'] = $categories;
if ($archives =$this->parsetml($sitebar, 'archives', '')) $widgets['archives'] = $archives;
if ($links =$this->parsetml($sitebar, 'links', '')) $widgets['links'] = $links;
if ($posts =$this->parsetml($sitebar, 'posts', '')) {
if ($item = $this->parsetml($posts, 'item', '%s')) $widgets['post'] = str_replace('"', '\"', $item);
$widgets['posts'] = $posts;
}
if ($comments =$this->parsetml($sitebar, 'comments', '')) {
if ($item = $this->parsetml($comments, 'item', '%s')) $widgets['comment'] = $item;
$widgets['comments'] = $comments;
}
if ($meta =$this->parsetml($sitebar, 'meta', '')) $widgets['meta'] = $meta;
$index++;
}
return $index;
}

}//class

?>