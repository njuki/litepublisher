<?php

class TFiles extends TItems {
  public $downloads;
  public $images;
  
  public static function &Instance() {
    return GetInstance(__class__);
  }
  
  protected function CreateData() {
    parent::CreateData();
    $this->CacheEnabled = false;
    $this->basename = 'files';
    $this->AddEvents('Changed', 'Edited');
    $this->AddDataMap('downloads', array());
    $this->AddDataMap('images', array());
    $this->Data['path'] = '/files/';
  }
  
  public function Reqest($args) {
    global $Options, $Urlmap;
    if (isset($_GET['fileid'])) {
      $id = $_GET['fileid'];
      if (isset($this->items[$id])) {
        $this->downloads[$id]++;
        $this->Save();
        $url = $this->Geturl($id);
        return "<?php @header('Location: $Options->url$url'); ?>";
      }
    }
    
    $Urlmap->NotFound404();
    return true;
  }
  
  public function Geturl($id) {
    return $this->path . $this->items[$id]['filename'];
  }
  
  public function Getlink($id) {
    global $Options;
    return '<a href="'. $Options->url . $this->Geturl($id) . '">'. $this->items[$id]['title'] . '</a>';
  }
  
  public function AddFile($filename, $content, $title, $overwrite = true) {
    if ($title == '') $title = $filename;
    $linkgen = &TLinkGenerator::Instance();
    $filename = $linkgen->FilterFileName($filename);
    $filename = $this->Upload($filename, $content, $overwrite);
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
  
  public function Delete($id) {
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