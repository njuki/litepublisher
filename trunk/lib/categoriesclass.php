<?php

class TCategories extends TCommonTags {
  private $contents;
  //public  $defaultid;
  
  protected function CreateData() {
    parent::CreateData();
    $this->contents = array();
    $this->basename = 'categories' ;
    $this->Data['defaultid']=  1;
  }
  
  public function Setdefaultid($id) {
    if (($id != $this->defaultid) && isset($this->items[$id])) {
      $this->Data['defaultid'] = $id;
      $this->Save();
    }
  }
  
  public static function &Instance() {
    return GetNamedInstance('categories', __class__);
  }
  
  public function Delete($id) {
    parent::Delete($id);
    @unlink($this->GetContentFilename($id));
  }
  
  private function GetContentFilename($id) {
    global $paths;
    return $paths['data'] . 'categories' . DIRECTORY_SEPARATOR . $id . '.php';
  }
  
  public function GetItemContent($id) {
    if (!isset($this->contents[$id])) {
      if (!TFiler::UnserializeFromFile($this->GetContentFilename($id), $this->contents[$id])) $this->contents[$id] = false;
    }
    return $this->contents[$id];
  }
  
  public function SetItemContent($id, $content) {
    $this->contents[$id] = array(
    'content' => $content,
    'excerpt' => TContentFilter::GetExcerpt($content, 80)
    );
    
    TFiler::SerializeToFile($this->GetContentFilename($id), $this->contents[$id]);
  }
  
  public function Getdescription() {
    if ($item = $this->GetItemContent($this->id)) {
      return $item['excerpt'];
    }
    return '';
  }
  
  public function Getkeywords() {
    return $this->title;
  }
  
  public function GetTemplateContent() {
    $result = '';
    if ($item = $this->GetItemContent($this->id)) {
      $result .= $item['content'];
    }
    
    $result .= parent::GetTemplateContent();
    return $result;
  }
  
}//class
?>