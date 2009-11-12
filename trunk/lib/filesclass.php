<?php

class tfiles extends TItems {
public $itemsposts;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
$this->itemsposts = new titemsposts();
    $this->basename = 'files';
$this->table = 'files';
    $this->addevents('Changed', 'Edited');
  }
  
  public function geturl($id) {
$item = $this->getitem($id);
    return '/files/' . $item['filename'];
  }
  
  public function getlink($id) {
    global $options;
$item = $this->getitem($id);
$icon = '';
if ($item['icon'] != 0) {
$icons = ticons::instance();
$icon = sprintf('<img src="%s" alt="%s" />', $icons->geturl($item['icon']), $item['title']);
}
    return sprintf('<a href="%1$s" title="%2$s">%3$s</a>', $options->files. $item['filename'], $item['title'], $icon . $item['description']);
  }
  
  public function upload($filename, $content, $title, $overwrite = true) {
    if ($title == '') $title = $filename;
    $linkgen = tlinkgenerator::instance();
    $filename = $linkgen->filterfilename($filename);
    $filename = $this->doupload($filename, $content, $overwrite);
    return $this->Add($filename, $title);
  }

public function Add($filename, $title) { 
global $options;
$mediaparser = tmediaparser::instance();
$info = $mediaparser->add($filename);

$item = array(
'medium' => $medium,
'parent' => 0,
'preview' => $preview,
'author' => $options->user,
'posted' => time(),
'icon' => $icon,
'filename' => $filename,
'title' => $title,
'description' => $description,
'keywords' => ''
);
return $this->additem($item);
}

private function additem(array $item) {
if (dbversion) {
return $this->db->add($item);
 } else {
    $this->items[++$this->autoid] = $item;
    $this->save();
    $this->changed();
    $this->added($this->autoid);
    return $this->autoid;
}
  }
  
   public function getunique($filename) {
    global $paths;
    if (!@file_exists($paths['files']. $filename)) return $filename;
    $parts = pathinfo($filename);
    $ext = empty($parts['extension']) ? '' : ".$parts[extension]";
    for ($i = 2; $i < 10000; $i++) {
      $filename = "$parts[filename]$i$ext";
      if  (!@file_exists($paths['files']. $filename)) break;
    }
    return $filename;
  }
  
  private function doupload($filename, &$content, $overwrite) {
    global $paths;
    if (!$overwrite) $filename = $this->getunique($filename);
    
    if (@file_put_contents($paths['files']. $filename, $content)) {
      $stat = @ stat($paths['files']);
      $perms = $stat['mode'] & 0007777;
      $perms = $perms & 0000666;
      @ chmod($paths['files']. $filename, $perms);
      return $filename;
    } else {
      return false;
    }
  }
  
  public function delete($id) {
    global $paths;
    if (!$this->itemexists($id)) return false;
    @unlink($paths['files']. $this->items[$id]['filename']);
parent::delete($id);
    $this->changed();
    return true;
  }
  
}//class

?>