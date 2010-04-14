<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tstdwidgets extends titems {
  public $disableajax;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  private function getinstance($name) {
    switch ($name) {
      case 'meta':
      return $this;
      
      case 'links':
      return tlinkswidget::instance();
      
      case 'comments':
      return tcommentswidget::instance();
      
      case 'friends':
      return tfoaf::instance();
      
      default:
      return litepublisher::$classes->$name;
    }
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'widgets.standarts';
    $this->dbversion = false;
    $this->disableajax = false;
    $this->data['names'] = array('categories', 'archives', 'links', 'friends', 'tags', 'posts', 'comments', 'meta');
    $this->data['meta'] = array(
    'rss' => true,
    'comments' => true,
    'media' => true,
    'foaf' => true,
    'profile' => true,
    'sitemap' => true
    );
  }
  
  public function add($name, $ajax, $sitebar) {
    if (isset($this->items[$name])) return $this->error("widget  $name already exists");
    if ($name == 'comments') {
      $manager = tcommentmanager::instance();
      $comwidget = tcommentswidget::instance();
      $manager->changed = $comwidget->changed;
    }
    $widgets = twidgets::instance();
    $id = $widgets->add($this->class, 'echo', $sitebar, -1);
    $this->items[$name] = array(
    'id' => $id,
    'ajax' => $ajax,
    'title' => $this->gettitle($name)
    );
    $this->save();
    return $id;
  }
  
  public function setajax($name, $ajax) {
    if (isset($this->items[$name]) && ($this->items[$name]['ajax'] != $ajax)) {
      $this->items[$name]['ajax'] = $ajax;
      $this->save();
    }
  }
  
  public function delete($name) {
    if (!isset($this->items[$name])) return;
    $widgets = twidgets::instance();
    $widgets->delete($this->items[$name]['id']);
    unset($this->items[$name]);
    $this->save();
  }
  
  public static function expired($name) {
    $self = self::instance();
    $self->expire($name);
  }
  
  public function expire($name) {
    if (!isset($this->items[$name])) return;
    $widgets = twidgets::instance();
    $widgets->itemexpired($this->items[$name]['id']);
  }
  
  public function widgetdeleted($id) {
    if ($name = $this->getname($id)) {
      unset($this->items[$name]);
      $this->save();
      
      if ($name == 'comments') {
        $manager = tcommentmanager::instance();
        $comwidget = tcommentswidget::instance();
        $manager->unsubscribeclass($comwidget);
      }
    }
  }
  
  public function gettitle($name) {
    return tlocal::$data['stdwidgetnames'][$name];
  }
  
  public function getname($id) {
    foreach ($this->items as $name => $item) {
      if ($id == $item['id']) return $name;
    }
    return false;
  }
  
  public function xmlrpcgetwidget($id) {
    if (!($name = $this->getname($id))) throw new Exception('Widget not found.', 404);
    $widgets = twidgets::instance();
    $result = $this->getwidgetcontent($id, $widgets->findsitebar($id));
    if ($name == 'comments') $result = file_get_contents($widgets->getcachefile($id));
    //fix for javascript xmlrpc
    if ($result == '') return 'false';
    return $result;
  }
  
  public function getwidget($id, $sitebar) {
    if (!($name = $this->getname($id))) return '';
    if (litepublisher::$options->icondisabled) {
      $icon = '';
    } else {
      $icons = ticons::instance();
      $icon = $icons->geticon($name);
    }
    
    $result = '';
    $title = $this->items[$name]['title'];
    if ($this->items[$name]['ajax'] && !$this->disableajax) {
      $title = "<a onclick=\"widgets.load(this, $id)\">$title</a>";
      $content = "<!--widgetcontent-$id-->";
    } else {
      $content = $this->getwidgetcontent($id, $sitebar);
    }
    
    $theme = ttheme::instance();
    $result .= $theme->getwidget($icon . $title, $content, $name, $sitebar);
    return $result;
  }
  
  public function getwidgetcontent($id, $sitebar) {
    if (!($name = $this->getname($id))) return '';
    $widgets = twidgets::instance();
    $file = $widgets->getcachefile($id);
    if (!file_exists($file)){
      if ($name == 'meta') {
        $result = $this->getmetacontent($id, $sitebar);
      } else {
        $instance = $this->getinstance($name);
        $result = $instance->getwidgetcontent($id, $sitebar);
      }
      file_put_contents($file, $result);
      @chmod($file, 0666);
    } else {
      if ($name != 'comments') return file_get_contents($file);
    }
    
    if ($name == 'comments')     return "\n<?php @include('$file'); ?>\n";
    return $result;
  }
  
  public function getmetacontent($id, $sitebar) {
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