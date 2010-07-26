<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class toldestposts extends tclasswidget {
  
  public static function instance() {
    return getinstance(__class__);
  }

  protected function create() {
    parent::create();
    $this->basename = 'widget.oldestposts';
$this->adminclass = 'tadminoldestposts';
$this->cache = 'nocache';
$this->data['title'] = tlocal::$data['default']['prev'];
    $this->data['maxcount'] = 10;
}
  
  public function getcontent($id, $sitebar) {
    $post = $this->getcontext('tpost');
    $posts = tposts::instance();
    if (dbversion) {
      $items = $posts->select("status = 'published' and posted < '$post->sqldate' ",' order by posted desc limit '. $this->maxcount);
    } else {
      $arch = array_keys($posts->archives);
      $i = array_search($post->id, $arch);
      if (!is_int($i)) return '';
      $items = array_slice($arch, $i + 1, $this->maxcount);
    }
    
    if (count($items) == 0) return '';
    
    $theme = ttheme::instance();
    $tml = $theme->getwidgetitem($this->template, $sitebar);
    $result = '';
    foreach ($items as $id) {
      $post = tpost::instance($id);
      ttheme::$vars['post'] = $post;
      $result .= sprintf($tml, $post->link, $post->title);
    }
    
    return sprintf($theme->getwidgetitems($this->template, $sitebar), $result);
  }
  
}//class

?>