<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class textrasidebars extends tplugin {
public $themes;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
$this->addmap('themes', array());
$this->data['beforepost'] = false;
$this->data['afterpost'] = true;
  }

  public function beforeparse(ttheme $theme, &$s) {
if (in_array($theme->name, $this->themes) && !isset($theme->data['extrasidebars'])) {
$s = &$theme->templates['index'];
if ($this->beforepost) $s .= '<!--$template.sidebar-->';
if ($this->afterpost) $s .= '<!--$template.sidebar-->';
}
}
  
  public function themeparsed(ttheme $theme) {
if (in_array($theme->name, $this->themes) && !isset($theme->data['extrasidebars'])) {
$s = &$theme->templates['index'];
$s = str_replace('<!--$template.sidebar-->', '', $s);
$sidebar = 0;
$tag = '$template.sidebar';
$i = 0;
while ($i = strpos($s, $tag, $i + 1)) {
$s = substr_replace($s, $tag . $sidebar++, $i, strlen($tag));
}

$theme->data['extrasidebars'] = $sidebar;
$post = &$theme->templates['content.post'];
if ($this->beforepost) $post = str_replace('$post.content', $tag . $sidebar++ . '$post.content', $post);
if ($this->afterpost) $post = str_replace('$post.content', '$post.content ' . $tag . $sidebar++, $post);
}
}

}//class