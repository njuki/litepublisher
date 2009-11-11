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
    return $this->path . $this->items[$id]['filename'];
  }
  
  public function getlink($id) {
    global $options;
    return '<a href="'. $options->url . $this->Geturl($id) . '">'. $this->items[$id]['title'] . '</a>';
  }
  
  public function add($filename, $content, $title, $overwrite = true) {
    if ($title == '') $title = $filename;
    $linkgen = tlinkgenerator::instance();
    $filename = $linkgen->FilterFileName($filename);
    $filename = $this->upload($filename, $content, $overwrite);
    return $this->Add($filename, $title);
  }
  
  public function GetUniqueFileName($filename) {
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
  
  protected function Upload($filename, &$content, $overwrite) {
    global $paths;
    if (!$overwrite) $filename = $this->GetUniqueFileName($filename);
    
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
  
  function Add($filename, $title) {
    $this->items[++$this->autoid] = array(
    'filename' => $filename,
    'title' => $title,
    'posts' => array()
    );
    $this->downloads[$this->autoid] = 0;
    $this->Save();
    $this->Changed();
    $this->Added($this->autoid);
    return $this->autoid;
  }
  
  public function AddPost($id, $postid) {
    if (!isset($this->items[$id]))  return false;
    if (in_array($postid, $this->items[$id]['posts'])) return  true;
    $this->items[$id]['posts'][] = $postid;
    $this->Save();
    $this->Changed();
    return true;
  }
  
  public function delete($id) {
    global $paths;
    if (!isset($this->items[$id])) return false;
    @unlink($paths['files']. $this->items[$id]['filename']);
    unset($this->items[$id]);
    //if (isset($this->downoads[$id])) unset($this->downoads[$id]);
    $this->Save();
    $this->Changed();
    $this->Deleted($id);
    return true;
  }
  
}//class

?>