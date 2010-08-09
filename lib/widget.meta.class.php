<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tmetawidget extends twidget {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'widget.meta';
    $this->template = 'meta';
    $this->adminclass = 'tadminmetawidget';
    $this->data['meta'] = array(
    'rss' => true,
    'comments' => true,
    'media' => true,
    'foaf' => true,
    'profile' => true,
    'sitemap' => true
    );
  }
  
  public function getdeftitle() {
    return tlocal::$data['default']['meta'];
  }
  
  public function getcontent($id, $sitebar) {
    extract($this->data['meta'], EXTR_SKIP);
    $result = '';
    $theme = ttheme::instance();
    $tml = $theme->getwidgetitem('meta', $sitebar);
    $metaclasses = isset($theme->data['sitebars'][$sitebar]['meta']) ? $theme->data['sitebars'][$sitebar]['meta']['classes'] :
    array('rss' => '', 'comments' => '', 'media' => '', 'foaf' => '', 'profile' => '', 'sitemap' => '');
    $lang = tlocal::instance('default');

    if ($rss) $result .= $this->getitem($tml, '/rss.xml', $lang->rss, $metaclasses['rss']);
    if ($comments) $result .= $this->getitem($tml, '/comments.xml', $lang->rsscomments, $metaclasses['comments']);
    if ($media) $result .= $this->getitem($tml, '/rss/multimedia.xml', $lang->rssmedia, $metaclasses['media']);
    if ($foaf) $result .= $this->getitem($tml, '/foaf.xml', $lang->foaf, $metaclasses['foaf']);
    if ($profile) $result .= $this->getitem($tml, '/profile.htm', $lang->profile, $metaclasses['profile']);
    if ($sitemap) $result .= $this->getitem($tml, '/sitemap.htm', $lang->sitemap, $metaclasses['sitemap']);
    
    if ($result == '') return '';
    return $theme->getwidgetcontent($result, 'meta', $sitebar);
  }

private function getitem($tml, $url, $title, $class) {
$args = targs::instance();
$args->icon = '';$args->subitems = '';
$args->rel = $class;
$args->url = litepublisher::$options->url  . $url;
$args->title = $title;
$args->anchor = $title;
$args->class = $class == '' ? '' : sprintf('class="%s"', $class);
$theme = ttheme::instance();
return $theme->parsearg($tml, $args);
}
  
}//class
?>