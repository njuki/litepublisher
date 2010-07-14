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
    $this->data['meta'] = array(
    'rss' => true,
    'comments' => true,
    'media' => true,
    'foaf' => true,
    'profile' => true,
    'sitemap' => true
    );
  }
  
public function gettitle($id) {
return tlocal::$data['stdwidgetnames']['meta'];
}

  public function getcontent($id, $sitebar) {
    extract($this->data['meta']);
    $result = '';
    $theme = ttheme::instance();
    $tml = $theme->getwidgetitem('meta', $sitebar);
    $tml .= "\n";
    $metaclasses = isset($theme->data['sitebars'][$sitebar]['meta']) ? $theme->data['sitebars'][$sitebar]['meta']['classes'] :
    array('rss' => '', 'comments' => '', 'media' => '', 'foaf' => '', 'profile' => '', 'sitemap' => '');
    $lang = tlocal::instance('default');
    $result = '';
    if ($rss) $result .= sprintf($tml, litepublisher::$options->url . '/rss.xml', $lang->rss, $metaclasses['rss']);
    if ($comments) $result .= sprintf($tml, litepublisher::$options->url . '/comments.xml', $lang->rsscomments, $metaclasses['comments']);
    if ($media) $result .= sprintf($tml, litepublisher::$options->url . '/rss/multimedia.xml', $lang->rssmedia, $metaclasses['media']);
    if ($foaf) $result .= sprintf($tml, litepublisher::$options->url . '/foaf.xml', $lang->foaf, $metaclasses['foaf']);
    if ($profile) $result .= sprintf($tml, litepublisher::$options->url . '/profile.htm', $lang->profile, $metaclasses['profile']);
    if ($sitemap) $result .= sprintf($tml, litepublisher::$options->url . '/sitemap.htm', $lang->sitemap, $metaclasses['sitemap']);
    
    if ($result == '') return '';
    return sprintf($theme->getwidgetitems('meta', $sitebar), $result);
  }
  
}//class
?>