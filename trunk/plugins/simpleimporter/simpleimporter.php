<?php

class TSimpleImporter extends TImporter {
public $items;

  public static function &Instance() {
  return GetInstance(__class__);
 }

 protected function CreateData() {
  parent::CreateData();
$this->Data['extra'] = '';
$this->AddDataMap('items', array(
'title' => 'title',
'link' => 'link',
'pubDate' => 'pubdate',
'content:encoded' => 'content'
));
}

public function Getcontent() {
global $Options;
$result = parent::Getcontent();
//$tml = file_get_contents(dirname(__file__) . DIRECTORY_SEPARATOR . 'simpleimporter.tml';
$dir = dirname(__file__) . DIRECTORY_SEPARATOR;
$html = THtmlResource::Instance();
$html->LoadIni($dir . 'simpleimporter.ini');
$html->section = 'simpleimporter';
TLocal::LoadIni($dir . 'about.ini');
TLocal::LoadIni($dir . "$Options->language.ini");
$lang = TLocal::Instance();
$lang->section = 'simpleimporter';

$result .= $html->options($this->GetItemsStr(), $this->extra);
return $result;
}

public function ProcessForm() {
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

$urlmap = TUrlmap::Instance();
$urlmap->lock();
$cats = TCategories::Instance();
$cats->lock();
$tags = TTags::Instance();
$tags->lock();
$posts = TPosts::Instance();
$posts->lock();
foreach ($a['rss']['channel'][0]['item'] as $item) {
$post = $this->add($item);
$posts->Add($post);
//echo $post->id, "<br>\n";
if (!TDataClass::$GlobalLock) $post->free();
//echo "<pre>\n";
//var_dump($post->Data);
}
$posts->unlock();
$tags->unlock();
$cats->unlock();
$urlmap->unlock();
}

public function add($item) {
$post = TPost::Instance();
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