<?php

class TPosts extends TItems {
  public $archives;
  //public $recentcount;
  
  protected function CreateData() {
    parent::CreateData();
    $this->basename = 'posts'  . DIRECTORY_SEPARATOR  . 'index';
    $this->AddEvents('Edited', 'Changed', 'SingleCron');
    $this->AddDataMap('archives' , array());
    $this->Data['recentcount'] = 10;
  }
  
  public function &GetItem($id) {
    if (isset($this->items[$id])) {
      return TPost::Instance($id);
    }
    return $this->Error("Item $id not found in class ". get_class($this));
  }
  
  public function Setrecentcount($value) {
    if ($value != $this->recentcount) {
      $this->Data['recentcount'] = $value;
      $this->Save();
    }
  }
  
  public function GetWidgetContent($id) {
    global $Options;
    $Template = TTemplate::Instance();
    $item = !empty($Template->theme['widget']['recentpost']) ? $Template->theme['widget']['recentpost'] :
    '<li><strong><a href=\'$Options->url$post->url\' rel=\'bookmark\' title=\'Permalink to $post->title\'>$post->title</a></strong><br />
    <small>$post->localdate</small></li>';
    
    $result = $Template->GetBeforeWidget('recentposts');
    $list = $this->GetRecent($this->recentcount);
    foreach ($list as $id) {
      $post = &TPost::Instance($id);
      eval('$result .= "'. $item . '\n";');
    }
    $result = str_replace("'", '"', $result);
    $result .= $Template->GetAfterWidget();
    return $result;
  }
  
  public function Add(&$Post) {
    global $paths;
    $this->Lock();
    $Post->id = ++$this->lastid;
    $dir =$paths['data'] . 'posts' . DIRECTORY_SEPARATOR  . $Post->id;
    @mkdir($dir, 0777);
    @chmod($dir, 0777);
    
    if ($Post->date == 0) $Post->date = time();
    $Post->modified = time();
    $Linkgen = &TLinkGenerator::Instance();
    if ($Post->url == '' ) {
      $Post->url = $Linkgen->Create($Post, 'post');
    } else {
      $title = $Post->title;
      $Post->title = trim($Post->url, '/');
      $Post->url = $Linkgen ->Create($Post, 'post');
      $Post->title = $title;
    }
    
    $this->Updated($Post);
    $Post->Save();
    $this->Unlock();
    $this->Added($Post->id);
    $this->Changed();
    
    $Urlmap = &TUrlmap::Instance();
    $Urlmap->Lock();
    $Urlmap->Add($Post->url, get_class($Post), $Post->id);
    $Urlmap->ClearCache();
    $Urlmap->Unlock();
  }
  
  public function Edit(&$Post) {
    $Urlmap = &TUrlmap::Instance();
    $this->Lock();
    
    $oldurl = $Urlmap->Find(get_class($Post), $Post->id);
    if ($oldurl != $Post->url) {
      $Urlmap->Lock();
      $Urlmap->Delete($oldurl);
      $Linkgen = &TLinkGenerator::Instance();
      if ($Post->url == '') {
        $Post->url = $Linkgen->Create($Post, 'post');
      } else {
        $title = $Post->title;
        $Post->title = trim($Post->url, '/');
        $Post->url = $Linkgen->Create($Post, 'post');
        $Post->title = $title;
      }
      $Urlmap->Add($Post->url, get_class($Post), $Post->id);
      $Urlmap->Unlock();
    }
    
    if ($oldurl != $Post->url) {
      $Urlmap->AddRedir($oldurl, $Post->url);
    }
    
    $Post->modified = time();
    $this->Updated($Post);
    $Post->Save();
    $this->Unlock();
    
    $Urlmap->ClearCache();
    
    $this->Edited($Post->id);
    $this->Changed();
  }
  
  public function Delete($id) {
    global $paths;
    if (!$this->ItemExists($id)) return false;
    $this->Lock();
    $post = &TPost::Instance($id);
    
    $Urlmap = &TUrlmap::Instance();
    $Urlmap->Lock();
    $Urlmap->Delete($post->url);
    $Urlmap->ClearCache();
    $Urlmap->Unlock();
    
    unset($this->items[$id]);
    TItem::DeleteItemDir($paths['data']. 'posts'. DIRECTORY_SEPARATOR   . $id . DIRECTORY_SEPARATOR  );
    $this->UpdateArchives();
    $this->Unlock();
    $this->Deleted($post->id);
    $this->Changed();
    return true;
  }
  
  public function Updated(&$post) {
    if (($post->status == 'published') && ($post->date > time())) {
      $post->status = 'future';
    }
    $this->items[$post->id] = array(
    'date' => $post->date
    );
    if   ($post->status != 'published') $this->items[$post->id]['status'] = $post->status;
    $this->UpdateArchives();
    $Cron = &TCron::Instance();
    $Cron->Add('single', get_class($this), 'DoSingleCron', $post->id);
  }
  
  public function UpdateArchives() {
    $this->PublishFuture();
    $this->archives = array();
    foreach ($this->items as $id => $item) {
      if ((!isset($item['status']) || ($item['status'] == 'published')) &&(time() >= $item['date'])) {
        $this->archives[$id] = $item['date'];
      }
    }
    arsort($this->archives,  SORT_NUMERIC);
  }
  
  public function DoSingleCron($id) {
    $this->PublishFuture();
    $GLOBALS['post'] = &TPost::Instance($id);
    $this->SingleCron($id);
    //ping
  }
  
  public function HourCron() {
    $this->PublishFuture();
  }
  
  public function PublishFuture() {
    foreach ($this->items as $id => $Item) {
      if (isset($item['status']) && ($Item['status'] == 'future') && ($Item['date'] <= time())) {
        $Post = TPost::Instance($id);
        $Post->status = 'published';
        $this->Edit($Post);
      }
    }
  }
  
  public function GetRecent($count) {
    return array_slice(array_keys($this->archives), 0, $count);
  }
  
  public function &GetPublishedRange($PageNum, $CountPerPage) {
    $Result= array();
    $Count = count($this->archives);
    $From = ($PageNum - 1) * $CountPerPage;
    if ($From > $Count)  return $Result;
    $To = min($From + $CountPerPage, $Count);
    $Result= array_slice(array_keys($this->archives), $From, $To - $From);
    return $Result;
  }
  
  public function StripDrafts(&$items) {
    return array_intersect($items, array_keys($this->archives));
  }
  
  public function SortAsArchive($items) {
    $result = array();
    foreach ($items as  $id) {
      if (isset($this->archives[$id])) {
        $result[$id] = $this->archives[$id];
      }
    }
    
    arsort($result,  SORT_NUMERIC);
    return array_keys($result);
  }
  
  //statics
  
  public static function &Instance() {
    return GetNamedInstance('posts', __class__);
  }
  
  public static function unsub(&$obj) {
    $self = self::Instance();
    $self->UnsubscribeClassName(get_class($obj));
  }
  
}

?>