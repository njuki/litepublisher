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
 
  public function themeparsed($theme) { 
if (empty($theme->templates['custom']['downloadexcerpt'])) {
  $dir = dirname(__file__) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;
    tlocal::loadsection('', 'downloaditem', $dir);
     ttheme::$vars['lang'] = tlocal::instance('downloaditem');
$theme->templates['custom']['downloadexcerpt'] = file_get_contents($dir . 'downloadexcerpt.tml');
$theme->templates['custom']['downloaditem'] = file_get_contents($dir . 'downloaditem.tml');
$theme->templates['custom']['siteform'] = $theme->parse(file_get_contents($dir . 'siteform.tml'));

//admin
$theme->templates['customadmin']['downloadexcerpt'] = array(
'type' => 'text',
'title' => 'Download excerpt'
);

$theme->templates['custom']['downloaditem'] = array(
'type' => 'text',
'title' => 'Download links'
);

$theme->templates['custom']['siteform'] = array(
'type' => 'text',
'title' => 'Upload site form'
);
}
}

}//class