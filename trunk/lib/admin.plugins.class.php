<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class tadminplugins extends tadminmenuitem {
private $abouts;

  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    //$this->adminplugins = array();
$this->readabout();
  }

private function readabout() {
global $options, $paths;
$this->abouts = array();
$list = tfiler::getdir($paths['plugins']);
sort($list);
foreach ($list as $name) {
$about = parse_ini_file($paths['plugins'] . $name . DIRECTORY_SEPARATOR . 'about.ini', true);
//слить языковую локаль в описание
if (isset($about[$options->language])) {
$about['about'] = $about[$options->language] + $about['about'];
}
$this->abouts[$name] = $about['about'];
    }
}
  
  public function getcontent() {
$result = '';
$html = $this->html;
//сделать список ссылок на админки установленных плагинов
$submenu = '';
$submenuitem = $html->submenuitem . "\n";
$url = $options->url . $this->url . $options->q . 'plugin=';
$plugins = tplugins::instance();
foreach ($this->abouts as $name) {
      if (isset($plugins->items[$name]) && !empty($about['adminclassname'])) {
          //$this->adminplugins[$name] = $about;
$submenu .= sprintf($submenuitem, $url, $this->abouts[$name]['name']);
        }
}
if ($submenu != '') $result .= sprintf($html->submenu, $submenu);

if (empty($_GET['plugin'])) {
      $result = $html->checkallscript;
$result .= $html->formhead();
$args = targs::instance();
foreach ($this->abouts as $name => $about) {
$args->name = $name;
$args->checked = isset($plugins->items[$name]);
$args->version = $about['version'];
$args->short = $about['name'];
$args->description = $about['description'];
$args->url = $about['url'];
$args->author = $about['author'];
$result .= $html->item($args);
}
$result .= $html->formfooter();
      $result = $this->FixCheckall($result);
} else {
$name = $_GET['plugin'];
if (!isset($this->abouts[$name])) return $this->notfound;
if ($admin = $this->getadmin($name)) {
$result .= $admin->getcontent();
}
}

      return $result;
  }

  public function processform() {
    global $options, $urlmap;
      $plugins = tplugins::instance();
if (empty($_GET['plugin'])) {
      $list = array_keys($_POST);
      array_pop($list);
      $plugins->update($list);
$result = $this->html->h2->updated;
} else {
      $name = $_GET['plugin'];
if (!isset($plugins[$name])) return $this->notfound;
if ($admin = $this->getadmin($name)) {
$result = $admin->processform();
}
    }
    
    $urlmap->clearcache();
    return $result;
  }
  
  private function getadminplugin($name) {
    global $paths;
$about = $this->abouts[$name];
      if (empty($about['adminclassname'])) return false;
      $class = $about['adminclassname'];
      if (!class_exists($class))  require_once($paths['plugins'] . $name . DIRECTORY_SEPARATOR . $about['adminfilename']);
return  getinstance($class );
 }
  
}//class
?>