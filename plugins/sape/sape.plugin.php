<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tsapeplugin extends twidget {
  public $sape;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
$this->basename = 'widget.sape';
$this->cache = 'nocache';
$this->data['title'] = tlocal::$data['default']['links'];
$this->data['user'] = '';
    $this->data['count'] = 2;
    $this->data['force'] = false;
    $this->data['optcode'] = '';
  }
  
  private function createsape() {
    if (!defined('_SAPE_USER')){
      define('_SAPE_USER', $this->user);
      require_once(dirname(__file__) . DIRECTORY_SEPARATOR . 'sape.php');
      $o['charset'] = 'UTF-8';
      $o['multi_site'] = true;
      if ($this->force) $o['force_show_code'] = $this->force;
      $this->sape = new SAPE_client($o);
    }
  }

public function gettitle($id) {
if ($is_null($id)) return tlocal::$data['default']['links'];
$widgets = twidgets::instance();
return $widgets->items[$id]['title'];
}

public function settitle($id, $title) {
$widgets = twidgets::instance();
$widgets->items[$id]['title'] = $title;
}
  
  public function getcontent($id, $sitebar) {
    if ($this->user == '') return '';
    if (litepublisher::$urlmap->is404 || litepublisher::$urlmap->adminpanel) return '';
    if (!isset($this->sape)) $this->createsape();
    $Links = $this->sape->return_links($$this->count);
    if (empty($Links)) return '';
    return sprintf('<ul><li>%s</li></ul>', $links);
  }
  
  public function onsitebar(&$content, $index) {
    $code = $this->tag;
    while ($i = strpos($content, $code)) {
      if ($links = $this->getlinks($this->count)) {
      $content = substr_replace($content, $links, $i, strlen($code));
    }
}
  }
  
}//class
?>