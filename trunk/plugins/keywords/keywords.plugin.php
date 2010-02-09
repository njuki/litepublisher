<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class tkeywordsplugin extends tplugin {
 protected $links;
 
 public static function instance() {
  return getinstance(__class__);
 }
 
 public function create() {
  parent::create();
  $this->basename = 'keywords' . DIRECTORY_SEPARATOR   . 'index';
  $this->data['count'] = 6;
  $this->addmap('links', array());
 }
 
public function getwidget($id, $sitebar) {
  global $options, $urlmap, $paths;
  if ($urlmap->adminpanel) return '';
  if ('/croncron.php' == substr($urlmap->url, 0, strlen('/croncron.php'))) return '';

  $filename = $paths['data'] . 'keywords' . DIRECTORY_SEPARATOR. $urlmap->itemrequested['id'] . ".$urlmap->page .php";
    if (@file_exists($filename)) {
   $links = file_get_contents($filename);
  } else {
   if (count($this->links) < $this->count) return '';
$arlinks = array_splice($this->links, 0, $this->count);
   $this->Save();

   $links = "\n<li>" . implode("</li>\n<li>", $arlinks)  . "</li>";
   file_put_contents($filename, $links);
  }
$theme = ttheme::instance();
  return $theme->getwidget(tlocal::$data['default']['keywords'], $links, 'widget', $sitebar);
 }
 
public function urldeleted($id) {
}

}
?>