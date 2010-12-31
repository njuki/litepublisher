<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminplugins extends tadminmenu {
  private $names;
  
  public static function instance($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  protected function create() {
    parent::create();
    $this->names = tfiler::getdir(litepublisher::$paths->plugins);
    sort($this->names);
  }
  
  public function getpluginsmenu() {
    $result = '';
    $url = $this->url . litepublisher::$site->q . 'plugin=';
    $plugins = tplugins::instance();
    foreach ($this->names as $name) {
      $about = tplugins::getabout($name);
      if (isset($plugins->items[$name]) && !empty($about['adminclassname'])) {
        $result .= sprintf('<li><a href="%s%s">%s</a></li>', $url, $name, $about['name']);
      }
    }
    
    return sprintf('<ul>%s</ul>', $result);
  }
  
  public function getcontent() {
    $result = $this->getpluginsmenu();
    $html = $this->html;
    $plugins = tplugins::instance();
    if (empty($_GET['plugin'])) {
      $result .= $html->formhead();
      $args = targs::instance();
      foreach ($this->names as $name) {
        $about = tplugins::getabout($name);
        $args->add($about);
        $args->name = $name;
        $args->checked = isset($plugins->items[$name]);
        $args->short = $about['name'];
        $result .= $html->item($args);
      }
      $result .= $html->formfooter();
      $result = $html->fixquote($result);
    } else {
      $name = $_GET['plugin'];
      if (!in_array($name, $this->names)) return $this->notfound;
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
      if (!in_array($name, $this->names)) return $this->notfound;
      if ($admin = $this->getadminplugin($name)) {
        $result = $admin->processform();
      }
    }
    
    litepublisher::$urlmap->clearcache();
    return $result;
  }
  
  private function getadminplugin($name) {
    $about = tplugins::getabout($name);
    if (empty($about['adminclassname'])) return false;
    $class = $about['adminclassname'];
    if (!class_exists($class))  require_once(litepublisher::$paths->plugins . $name . DIRECTORY_SEPARATOR . $about['adminfilename']);
    return  getinstance($class );
  }
  
}//class
?>