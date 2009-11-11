<?php

class tfiles extends TItems {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->CacheEnabled = false;
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
    return sprintf('<a href="%1$s" title="%2$s">%3$s</a>', $options->files. $item['filename'], $item['title'], $item['description']);
  }
  
  public function upload($filename, $content, $title, $overwrite = true) {
    if ($title == '') $title = $filename;
    $linkgen = tlinkgenerator::instance();
    $filename = $linkgen->FilterFileName($filename);
    $filename = $this->doupload($filename, $content, $overwrite);
    return $this->Add($filename, $title);
  }

  function Add($filename, $title) {
$item = array(
'parent' => $parent,
'preview' => $previe,
'mediatype' => $mediatype,
'author' => $options->user,
'posted' => time(),
'icon' =>=> $icon,
itemscount  int => 0,
'filename' => $filename,
'title' => $title,
'description' => $description
);
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