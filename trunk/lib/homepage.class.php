<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class thomepage extends tmenu  {
  
  public static function instance($id = 0) {
    return $id == 0 ? self::singleinstance(__class__) : self::iteminstance(__class__, $id);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'homepage' ;
    $this->data['image'] = '';
    $this->data['hideposts'] = false;
  }
  
public function gettitle() {}
  
  public function getcont() {
    $result = '';
    $theme = ttheme::instance();
    if (litepublisher::$urlmap->page == 1) {
      $image = $this->image;
      if ($image != '') {
        if (!strbegin($image, 'http://')) $image = litepublisher::$site->files . $image;
        $image = sprintf('<img src="%s" algt="Home image" />', $image);
      }
      $result .= $theme->simple($image . $this->content);
    }
    if ($this->hideposts) return $result;
    
    $items =  $this->getitems();
    $result .= $theme->getposts($items, false);
    $Posts = tposts::instance();
    $result .=$theme->getpages($this->url, litepublisher::$urlmap->page, ceil($Posts->archivescount / litepublisher::$options->perpage));
    return $result;
  }
  
  public function getitems() {
    $Posts = tposts::instance();
    return $Posts->GetPublishedRange(litepublisher::$urlmap->page, litepublisher::$options->perpage);
  }
  
}//class
?>