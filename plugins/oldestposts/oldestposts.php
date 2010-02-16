<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class toldestposts extends tplugin {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function onsitebar(&$content, $index) {
    if ($index > 0) return;
    $links = $this->getoldposts($index);
    if ($links == '') return;
    $theme = ttheme::instance();
    $widget = $theme->getwidget(tlocal::$data['default']['prev'], $links, 'widget', $index);
    $content = $widget . $content;
  }
  
  private function getoldposts($index) {
    $template = ttemplate::instance();
    $post = $template->context;
    $posts = tposts::instance();
    
    if (dbversion) {
      $items = $posts->select("status = 'published' and posted < '$post->sqldate' ",' order by posted desc limit 10');
    } else {
      $arch = array_keys($posts->archives);
      $i = array_search($post->id, $arch);
      if (!is_int($i)) return '';
      $items = array_slice($arch, $i + 1, 10);
    }
    
    if (count($items) == 0) return '';
    
    $theme = ttheme::instance();
    $tml = $theme->getwidgetitem('widget', $index);
    $result = '';
    foreach ($items as $id) {
      ttheme::$vars['post'] = tpost::instance($id);
      $result .= sprintf($tml, $post->link, $post->title);
    }
    return $result;
  }
  
}//class
?>