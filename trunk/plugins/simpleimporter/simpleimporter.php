<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class tsimpleimporter extends timporter {
public $items;

  public static function instance() {
  return getinstance(__class__);
 }

 protected function create() {
  parent::create();
$this->data['extra'] = '';
$this->addmap('items', array(
'title' => 'title',
'link' => 'link',
'pubDate' => 'pubdate',
'content:encoded' => 'content'
));
}

public function getcontent() {
global $options;
$result = parent::getcontent();
//$tml = file_get_contents(dirname(__file__) . DIRECTORY_SEPARATOR . 'simpleimporter.tml';
$dir = dirname(__file__) . DIRECTORY_SEPARATOR;
$html = THtmlResource::instance();
$html->loadini($dir . 'simpleimporter.ini');
$html->section = 'simpleimporter';

$result .= $html->options($this->GetItemsStr(), $this->extra);
return $result;
}

public function processform() {
if ($_POST['form'] != 'options')  return parent::ProcessForm();
$this->ParseItems($_POST['items']);
$this->extra = $_POST['extra'];
$this->save();
}

public function ParseItems($s) {
$this->items = array();
$lines = explode("\n", $s);
foreach ($lines as $line) {
if ($i = strpos($line, '=')) {
$key = trim(substr($line, 0, $i));
$val = trim(substr($line, $i + 1));
$this->items[$key] = $val;
}
}
}

public function GetItemsStr() {
$result = '';
foreach ($this->items as $key => $val) {
$result .= "$key = $val\n";
}
return $result;
}

public function import($s) {
global $paths;
require_once($paths['lib'] . 'domrss.php');
$a = xml2array($s);

$urlmap = TUrlmap::instance();
$urlmap->lock();
$cats = TCategories::instance();
$cats->lock();
$tags = TTags::instance();
$tags->lock();
$posts = TPosts::instance();
$posts->lock();
foreach ($a['rss']['channel'][0]['item'] as $item) {
$post = $this->add($item);
$posts->Add($post);
//echo $post->id, "<br>\n";
if (!TDataClass::$GlobalLock) $post->free();
//echo "<pre>\n";
//var_dump($post->data);
}
$posts->unlock();
$tags->unlock();
$cats->unlock();
$urlmap->unlock();
}

public function add($item) {
$post = TPost::instance();
foreach ($this->items as $key => $val) {
if (isset($item[$key])) {
$post->{$val} = $item[$key];
}
}
if ($this->extra != '') eval($this->extra);
return $post;
}

}//class

?>