<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
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
    $this->data['redir'] = false;
    $this->redirlink = '/addtobookmarks.htm';
  }
  
  public function getdeftitle() {
    $about = tplugins::getabout(tplugins::getname(__file__));
    return $about['name'];
  }
  
  public function getwidget($id, $sidebar) {
    $widgets = twidgets::instance();
    return $widgets->getinline($id, $sidebar);
  }
  
  public function getcontent($id, $sidebar) {
    if (litepublisher::$urlmap->is404) return '';
    $result = '';
    $a = array(
    '$url' => urlencode(litepublisher::$site->url .  litepublisher::$urlmap->url),
    '$title' => urlencode(ttemplate::instance()->title)
    );
    $redirlink = litepublisher::$site->url . $this->redirlink . litepublisher::$site->q . strtr('url=$url&title=$title&id=', $a);
    $iconurl = litepublisher::$site->files . sprintf('/plugins/%s/icons/', basename(dirname(__file__)));
    $theme = ttheme::instance();
    $tml = $theme->getwidgetitem('links', $sidebar);
    $args = targs::instance();
    $args->subcount = '';
    $args->subitems = '';
    $args->rel = 'link bookmark';
    foreach ($this->items as $id => $item) {
      $args->id = $id;
      $args->title = $item['title'];
      $args->text = $item['title'];
      if ($this->redir) {
        $args->link = $redirlink . $id;
      } else {
        $args->link = strtr($item['url'], $a);
      }
      
      $args->icon = $item['text'] == '' ? '' : sprintf('<img src="%s%s" alt="%s" />', $iconurl, $item['text'], $item['title']);
      $result .=   $theme->parsearg($tml, $args);
    }
    
    return $theme->getwidgetcontent($result, 'links', $sidebar);
  }
  
  public function request($arg) {
    $this->cache = false;
    $id = empty($_GET['id']) ? 1 : (int) $_GET['id'];
    if (!isset($this->items[$id])) return 404;
    $url = $this->items[$id]['url'];
    $a = array(
    '$url' => urlencode($_GET['url']),
    '$title' => urlencode($_GET['title'])
    );
    $url = strtr($url, $a);
    return turlmap::redir($url);
  }
  
}//class
?>