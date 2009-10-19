<?php

class TMenu extends TItems {
  protected $home;
  public $AcceptGet;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'menus' . DIRECTORY_SEPARATOR   . 'index';
    $this->AddDataMap('home', array());
    $this->addevents('BeforeAdd', 'Edited');
  }
  
  public function Add(&$Item) {
    //$this->Error("cantadd");
    global $paths;
    $this->Lock();
    $Item->Lock();
    $Item->id = ++$this->lastid;
    @mkdir($paths['data'] . 'menus'. DIRECTORY_SEPARATOR . $Item->id, 0777);
    @chmod($paths['data'] . 'menus'. DIRECTORY_SEPARATOR . $Item->id, 0777);
    
    $this->BeforeAdd($Item->id);
    if ($Item->date == 0) $Item->date = time();
    
    $Linkgen = &TLinkGenerator::instance();
    if ($Item->url == '') {
      $Item->url = $Linkgen->Create($Item, 'post');
    } else {
      $title = $item->title;
      $item->title = trim($item->url, '/');
      $item->url = $Linkgen->Create($item, 'post');
      $item->title = $title;
    }
    
    $this->UpdateInfo($Item);
    $Item->Unlock();
    $this->Unlock();
    
    $Urlmap = TUrlmap::instance();
    if ($this->AcceptGet) {
      $Urlmap->AddGet($Item->url, get_class($Item), $Item->id);
    } else {
      $Urlmap->Add($Item->url, get_class($Item), $Item->id);
    }
    
    $this->Added($Item->id);
    $Urlmap->ClearCache();
    return $Item->id;
  }
  
  public function Edit(&$item) {
    $Urlmap = TUrlmap::instance();
    $Urlmap->Lock();
    
    $this->Lock();
    
    $oldurl = $Urlmap->Find(get_class($item), $item->id);
    if ($oldurl != $item->url) {
      $Urlmap->Delete($oldurl);
      $Linkgen = &TLinkGenerator::instance();
      if ($item->url == '') {
        $item->url = $Linkgen->Create($item, 'post');
      } else {
        $title = $item->title;
        $item->title = trim($item->url, '/');
        $item->url = $Linkgen->Create($item, 'post');
        $item->title = $title;
      }
      $Urlmap->Add($item->url, get_class($item), $item->id);
    }
    
    if ($oldurl != $item->url) {
      $Urlmap->AddRedir($oldurl, $item->url);
    }
    
    $this->UpdateInfo($item);
    $item->Unlock();
    $this->Unlock();
    
    $Urlmap->Unlock();
    $Urlmap->ClearCache();
    
    
    $this->Edited($Item->id);
  }
  
  public function UpdateInfo(&$item) {
    $this->items[$item->id] = array(
    'id' => $item->id,
    'date' => $item->date,
    'status' => $item->status,
    'parent' =>$item->parent,
    'order' => $item->order,
    'childs' => array(),
    'title' => $item->title,
    'url' => $item->url,
    'class' => get_class($item)
    );
    $this->Sort();
  }
  
  public function Sort() {
    global $paths;
    $this->home = array();
    
    foreach ($this->items as $id => $item) {
      $this->items[$id]['childs'] = array();
      if (($item['parent'] == 0)  && ($item['status'] == 'published') && ($item['date'] <= time()) ) {
        $this->home[$id] = (int) $item['order'];
      }
    }
    
    foreach ($this->items as $id => $item) {
      if ($item['parent'] > 0) {
        $this->items[$item['parent']]['childs'][] = $id;
      }
    }
    
    arsort($this->home,  SORT_NUMERIC);
    $this->home = array_reverse($this->home, true);
    $this->Save();
  }
  
  public function GetHomeLinks() {
    global $Options;
    $Result = array();
    foreach ($this->home as $id => $order) {
      $title =  $this->items[$id]['title'];
      $Result[] = "<a href='". $Options->url . $this->items[$id]['url'] . "' title='$title'>$title</a>";
    }
    return $Result;
  }
  
  public function GetMenuList() {
    global $Options;
    $result = array();
    foreach ($this->home as $id => $order) {
      $subitems = array();
      if ($this->GetChildsCount($id) > 0) {
        foreach ($this->items[$id]['childs'] as $idchild) {
          $subitems[] = array(
          'url' => $Options->url . $this->items[$idchild]['url'],
          'title' =>  $this->items[$idchild]['title']
          );
        }
      }
      
      $result[] = array(
      'url' =>       $Options->url . $this->items[$id]['url'],
      'title' =>  $this->items[$id]['title'],
      'subitems' => $subitems
      );
    }
    return $result;
  }
  
  public function GetTitle($id) {
    return $this->items[$id]['title'];
  }
  
  public function  Delete($id) {
    global $paths;
    if (!$this->ItemExists($id)) return false;
    if ($this->GetChildsCount($id) > 0) return false;
    $this->Lock();
    $Urlmap = TUrlmap::instance();
    $Urlmap->Delete($this->items[$id]['url']);
    TItem::DeleteItemDir($paths['data']. 'menus'. DIRECTORY_SEPARATOR  . $id. DIRECTORY_SEPARATOR );
    unset($this->items[$id]);
    $this->Sort();
    $this->Unlock();
    $this->Deleted($id);
    $Urlmap->ClearCache();
    return true;
  }
  
  private function GetChildsCount($id) {
    return count($this->items[$id]['childs']);
  }
  
}
?>