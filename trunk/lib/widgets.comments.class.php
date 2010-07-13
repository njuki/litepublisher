<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tcommentswidget extends twidget {
  
  public static function instance() {
    return getinstance(__class__);
  }

protected function create() {
parent::create();
$this->basename = 'widget.comments';
$this->cache = 'include';
$this->template = 'comments';
}

public function gettitle() {
return tlocal::$data['stdwidgetnames']['comments'];
}

  public function getcontent($id, $sitebar) {
    $manager = tcommentmanager::instance();
    $recent = $manager->getrecent($manager->recentcount);
    if (count($recent) == 0) return '';
    $result = '';
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
$this->expired($this->id);
  }
  
}//class
?>