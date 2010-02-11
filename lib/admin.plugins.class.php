<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminplugins extends tadminmenu {
  public $abouts;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    //$this->adminplugins = array();
    $this->readabout();
  }
  
  private function readabout() {
    $this->abouts = array();
    $list = tfiler::getdir(litepublisher::$paths->plugins);
    sort($list);
    foreach ($list as $name) {
      $about = parse_ini_file(litepublisher::$paths->plugins . $name . DIRECTORY_SEPARATOR . 'about.ini', true);
      //слить языковую локаль в описание
      if (isset($about[litepublisher::$options->language])) {
        $about['about'] = $about[litepublisher::$options->language] + $about['about'];
      }
      $this->abouts[$name] = $about['about'];
    }
  }
  
  public function getsitebar() {
    $widgets = twidgets::instance();
    if ($widgets->current > 0) return $widgets->getcontent();
    $theme = ttheme::instance();
    $tml = $theme->getwidgetitem('submenu', 0);
    $args = targs::instance();
    $args->count = '';
    $url = $this->url . litepublisher::$options->q . 'plugin=';
    $content = '';
    $plugins = tplugins::instance();
    foreach ($this->abouts as $name => $about) {
      if (isset($plugins->items[$name]) && !empty($about['adminclassname'])) {
        $args->url = $url . $name;
        $args->title = $about['name'];
        $args->icon = '';
        $content .= $theme->parsearg($tml, $args);
      }
    }
    
    $result =     $theme->getwidget($this->title, $content, 'submenu', $widgets->current);
    $result .= $widgets->getcontent();
    return $result;
  }
  
  public function getcontent() {
    $result = '';
    $html = $this->html;
    $plugins = tplugins::instance();
    if (empty($_GET['plugin'])) {
      $result .= $html->checkallscript;
      $result .= $html->formhead();
      $args = targs::instance();
      foreach ($this->abouts as $name => $about) {
        $args->add($about);
        $args->name = $name;
        $args->checked = isset($plugins->items[$name]);
        $args->short = $about['name'];
        $result .= $html->item($args);
      }
      $result .= $html->formfooter();
      $result = $this->FixCheckall($result);
    } else {
      $name = $_GET['plugin'];
      if (!isset($this->abouts[$name])) return $this->notfound;
      if ($admin = $this->getadminplugin($name)) {
        $result .= $admin->getcontent();
      }
    }
    
    return $result;
  }
  
  public function processform() {
    if (!isset($_GET['plugin'])) {
      $list = array_keys($_POST);
      array_pop($list);
      $plugins = tplugins::instance();
      try {
        $plugins->update($list);
      } catch (Exception $e) {
        litepublisher::$options->handexception($e);
      }
      $result = $this->html->h2->updated;
    } else {
      $name = $_GET['plugin'];
      if (!isset($this->abouts[$name])) return $this->notfound;
      if ($admin = $this->getadminplugin($name)) {
        $result = $admin->processform();
      }
    }
    
    litepublisher::$urlmap->clearcache();
    return $result;
  }
  
  private function getadminplugin($name) {
    $about = $this->abouts[$name];
    if (empty($about['adminclassname'])) return false;
    $class = $about['adminclassname'];
    if (!class_exists($class))  require_once(litepublisher::$paths->plugins . $name . DIRECTORY_SEPARATOR . $about['adminfilename']);
    return  getinstance($class );
  }
  
}//class
?>