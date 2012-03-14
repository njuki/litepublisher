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
if ($this->beforepost) $theme->templates['sidebars'][] = array();
if ($this->afterpost) $theme->templates['sidebars'][] = array();
}
}
  
  public function themeparsed(ttheme $theme) {
if (in_array($theme->name, $this->themes) && !isset($theme->data['extrasidebars'])) {
$theme->data['extrasidebars'] = true;
if ($this->beforepost) $theme->templates['content.post'] = str_replace('$post.content', '$template.sidebar $post.content', $theme->templates['content.post']);
if ($this->afterpost) $theme->templates['content.post'] = str_replace('$post.content', '$post.content $template.sidebar', $theme->templates['content.post']);
}
}

}//class