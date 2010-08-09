<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tlinkswidget extends twidget {
  public $items;
  public $autoid;
  public $redirlink;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->addevents('added', 'deleted');
    $this->basename = 'widgets.links';
    $this->template = 'links';
    $this->adminclass = 'tadminlinkswidget';
    $this->addmap('items', array());
    $this->addmap('autoid', 0);
    $this->redirlink = '/linkswidget/';
    $this->data['redir'] = true;
  }
  
  public function getdeftitle() {
    return tlocal::$data['default']['links'];
  }
  
  public function getcontent($id, $sitebar) {
    if (count($this->items) == 0) return '';
    $result = '';
    $theme = ttheme::instance();
    $tml = $theme->getwidgetitem('links', $sitebar);
$redirlink = litepublisher::$options->url . $this->redirlink . litepublisher::$options->q . 'id=';
$url = litepublisher::$options->url;
    $args = targs::instance();
$args->subitems = '';
$args->icon = '';
$args->rel = 'link blogroll';
    foreach ($this->items as $id => $item) {
$args->add($item);
$args->id = $id;
      if ($this->redir && !strbegin($item['url'], $url)) {
        $args->url = $redirlink . $id;
      }
            $result .=   $theme->parsearg($tml, $args);
    }
    
    return $theme->getwidgetcontent($result, 'links', $sitebar);
  }
  
  public function add($url, $title, $anchor) {
    $this->items[++$this->autoid] = array(
    'url' => $url,
    'title' => $title,
    'anchor' => $anchor
    );
    
    $this->save();
    $this->added($this->autoid);
    return $this->autoid;
  }
  
  public function edit($id, $url, $title, $anchor) {
    $id = (int) $id;
    if (!isset($this->items[$id])) return false;
    $this->items[$id] = array(
    'url' => $url,
    'title' => $title,
    'anchor' => $anchor
    );
    $this->save();
  }
  
  public function delete($id) {
    if (isset($this->items[$id])) {
      unset($this->items[$id]);
      $this->save();
      litepublisher::$urlmap->clearcache();
    }
  }
  
  public function request($arg) {
    $this->cache = false;
    $id = empty($_GET['id']) ? 1 : (int) $_GET['id'];
    if (!isset($this->items[$id])) return 404;
  return "<?php @header('Location: {$this->items[$id]['url']}'); ?>";
  }
  
}//class

?>