<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tbookmarkswidget extends tlinkswidget  {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'widget.bookmarks';
    $this->cache = 'nocache';
    $this->redirlink = '/addtobookmarks.htm';
  }
  
  public function getdeftitle() {
    $about = tplugins::getabout(tplugins::getname(__file__));
    return $about['name'];
  }

  public function getcontent($id, $sitebar) {
if (litepublisher::$urlmap->is404) return '';
    $result = '';
$a = array(
'$url' => urlencode(litepublisher::$options->url .  litepublisher::$urlmap->url),
'$title' => urlencode(ttemplate::instance()->title)
);
$redirlink = litepublisher::$options->url . $this->redirlink . litepublisher::$options->q . strtr('url=$url&title=$title&id=', $a);
$iconurl = llitepublisher::$options->files . sprintf('/plugins/%s/icons/', basename(dirname(__file__)));
    $theme = ttheme::instance();
    $tml = $theme->getwidgetitem('links', $sitebar);
    $args = targs::instance();
    $args->subitems = '';
    $args->rel = 'link bookmark';
    foreach ($this->items as $id => $item) {
      $args->id = $id;
$args->title = $item['title'];
$args->anchor = $item['title'];
      if ($this->redir) {
        $args->url = $redirlink . $id;
      } else {
      $args->url = strtr($item['url'], $a);
}
$args->icon = $item['anchor'] == '' ? '' : sprintf('<img src="%s%s" alt="%s" />', $iconurl, $item['anchor'], $item['title']);
      $result .=   $theme->parsearg($tml, $args);
    }
    
    return $theme->getwidgetcontent($result, 'links', $sitebar);

}

}//class
?>