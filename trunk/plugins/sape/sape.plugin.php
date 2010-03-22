<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tsapeplugin extends tplugin {
  public $sape;
  public $widgets;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->data['user'] = '';
    $this->data['count'] = 2;
    $this->data['force'] = false;
    $this->data['optcode'] = '';
    $this->addmap('widgets', array());
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
  
  public static function echolinks($count = null) {
    $self = getinstance(__class__);
    echo $self->getlinks($count);
  }
  
  public function getlinks($count = null) {
    if ($this->user == '') return '';
    if (litepublisher::$urlmap->is404 || litepublisher::$urlmap->adminpanel) return '';
    if (!isset($this->sape)) $this->createsape();
    $Links = $this->sape->return_links($count);
    if (empty($Links)) return '';
    return "<li>$Links</li>\n";
  }
  
  protected function gettag() {
    return sprintf('<!--%s-->', $this->optcode);
  }
  
  public function getwidgetcontent($id, $sitebar) {
    return $this->tag;
  }
  
  public function onwidgetcontent($id, &$content) {
    if (in_array($id, $this->widgets)) {
      $content .= "\n<!--$this->optcode-->\n";
    }
  }
  
  public function onsitebar(&$content, $index) {
    $this->replacecode($content);
  }
  
  private function replacecode(&$content) {
    $code = $this->tag;
    while ($i = strpos($content, $code)) {
      $links = $this->getlinks($this->count);
      $content = substr_replace($content, $links, $i, strlen($code));
    }
  }
  
}//class
?>