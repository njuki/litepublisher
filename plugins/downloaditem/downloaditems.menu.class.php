<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tdownloaditemsmenu extends tmenu {
  
  public static function instance($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  protected function create() {
    parent::create();
    $this->data['type'] = '';
  }

  public function getcont() {
    $result = '';
    $theme = ttheme::instance();
    if (litepublisher::$urlmap->page == 1) {
      $result .= $theme->simple($theme->parse($this->content));
    }
$result .= $theme->templates['custom']['siteform'];

      $perpage = litepublisher::$options->perpage;
      $downloaditems = tdownloaditems::instance();
      $d = litepublisher::$db->prefix . $downloaditems->childtable;
      $p = litepublisher::$db->posts;
      $where = $this->type == '' ? '' : " and $d.type = '$this->type'";
      $count = $downloaditems->getchildscount($where);
      $from = (litepublisher::$urlmap->page - 1) * $perpage;
      if ($from <= $count)  {
        $items = $downloaditems->select("$p.status = 'published' $where", " order by $p.posted desc limit $from, $perpage");
    ttheme::$vars['lang'] = tlocal::instance('downloaditem');
    $tml = $theme->templates['custom']['downloadexcerpt'];
    foreach($items as $id) {
      ttheme::$vars['post'] = tdownloaditem::instance($id);
      $result .= $theme->parse($tml);
    }
}    
    $result .=$theme->getpages($this->url, litepublisher::$urlmap->page, ceil($count / $perpage));
    return $result;
  }
 
}//class
