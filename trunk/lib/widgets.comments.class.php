<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tcommentswidget extends tevents {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function getwidgetcontent($id, $sitebar) {
    $manager = tcommentmanager::instance();
    $recent = $manager->getrecent($manager->recentcount);
if (count($recent) == 0) return '';    $result = '';
    $theme = ttheme::instance();
    $tml = $theme->getwidgetitem('comments', $sitebar);
    $args = targs::instance();
    $args->onrecent = tlocal::$data['comment']['onrecent'];
    foreach ($recent as $item) {
      $args->add($item);
      $args->content = tcontentfilter::getexcerpt($item['content'], 120);
      $result .= $theme->parsearg($tml,$args);
    }
return sprintf($theme->getwidgetitems('comments', $sitebar), $result);
  }
  
  public function changed($id, $idpost) {
    $std = tstdwidgets::instance();
    $std->expire('comments');
  }
  
}//class
?>