<?php

class tposts extends TItems {
  public $archives;
  //public $recentcount;
  
  protected function create() {
    parent::create();
$this->table = 'posts';
    $this->basename = 'posts'  . DIRECTORY_SEPARATOR  . 'index';
    $this->addevents('edited', cChanged', 'singlecron');
    $this->AddDataMap('archives' , array());
    $this->data['recentcount'] = 10;
  }
  
  public function getitem($id) {
if (dbversion) {
    if ($res = $this->db->select("id = $id")) {
if ($result = tpost::instance($id)) return $result;
} else {
    if (isset($this->items[$id])) return tpost::instance($id);
    }
    return $this->error("Item $id not found in class ". get_class($this));
  }
  
  public function setrecentcount($value) {
    if ($value != $this->recentcount) {
      $this->data['recentcount'] = $value;
      $this->save();
    }
  }
  
  public function GetWidgetContent($id) {
    global $options;
    $template = template::instance();
    $item = !empty($template->theme['widget']['recentpost']) ? $template->theme['widget']['recentpost'] :
    '<li><strong><a href=\'$options->url$post->url\' rel=\'bookmark\' title=\'Permalink to $post->title\'>$post->title</a></strong><br />
    <small>$post->localdate</small></li>';
    
    $result = '';
    $list = $this->getrecent($this->recentcount);
    foreach ($list as $id) {
      $post = tpost::instance($id);
      eval('$result .= "'. $item . '\n";');
    }
    $result = str_replace("'", '"', $result);
    return $result;
  }
  
  public function add(TPost $post) {
    if (!isset($post)) return $this->Error('Post not assigned');
    if ($post->date == 0) $post->date = time();
    $post->modified = time();
    
    $Linkgen = TLinkGenerator::Instance();
    if ($post->url == '' ) {
      $post->url = $Linkgen->Create($post, 'post');
    } else {
      $title = $post->title;
      $post->title = trim($post->url, '/');
      $post->url = $Linkgen ->Create($post, 'post');
      $Post->title = $title;
    }
    
    if ($this->dbversion) {
      global $db;
      $post->pagescount = count($post->pages);
      $post->idurl = $urlmap->add($post->url, get_class($post), $post->id, $post->pagescount);
      
      $post->id = $this->db->IInsertRow();
      
      $db->table = 'rawcontent';
      $db->InsertAssoc(array(
      'id' => $post->id,
      'rawcontent' => $post->data['rawcontent']
      ));
      
      $db->table = 'pages';
      foreach ($post->pages as $i => $content) {
        $db->InsertAssoc(array(
        'post' => $post->id,
        'page' => $i,
        'content' => $content
        ));
      }
      
      $this->Updated($post);
    } else {
      global $paths;
      $post->id = ++$this->lastid;
      $dir =$paths['data'] . 'posts' . DIRECTORY_SEPARATOR  . $post->id;
      @mkdir($dir, 0777);
      @chmod($dir, 0777);
      
      $this->lock();
      $this->Updated($post);
      $post->save();
      $this->unlock();
    }
    $this->Added($post->id);
    $this->Changed();
    
    $urlmap = &TUrlmap::Instance();
    $urlmap->Add($post->url, get_class($post), $post->id);
    $urlmap->ClearCache();
    return $post->id;
  }
  
  public function Edit(TPost &$Post) {
    $urlmap = &TUrlmap::Instance();
    $this->lock();
    
    $oldurl = $urlmap->Find(get_class($Post), $Post->id);
    if ($oldurl != $Post->url) {
      $urlmap->lock();
      $urlmap->Delete($oldurl);
      $Linkgen = &TLinkGenerator::Instance();
      if ($Post->url == '') {
        $Post->url = $Linkgen->Create($Post, 'post');
      } else {
        $title = $Post->title;
        $Post->title = trim($Post->url, '/');
        $Post->url = $Linkgen->Create($Post, 'post');
        $Post->title = $title;
      }
      $urlmap->Add($Post->url, get_class($Post), $Post->id);
      $urlmap->unlock();
    }
    
    if ($oldurl != $Post->url) {
      $urlmap->AddRedir($oldurl, $Post->url);
    }
    
    $Post->modified = time();
    $this->Updated($Post);
    $Post->save();
    $this->unlock();
    
    $urlmap->ClearCache();
    
    $this->Edited($Post->id);
    $this->Changed();
  }
  
  public function Delete($id) {
    if (!$this->ItemExists($id)) return false;
    $urlmap = &TUrlmap::Instance();
    if ($this->dbversion) {
      global $db;
      $idurl = $this->db->idvalue($id, 'idurl');
      $urlmap->delete($idurl);
      $this->db->delete("id = $id limit 1");
      $db->table = 'pages';
      $db->delete("post = $id");
    } else {
      global $paths;
      
      $this->lock();
      $post = &TPost::Instance($id);
      
      $urlmap->lock();
      $urlmap->Delete($post->url);
      $urlmap->unlock();
      
      unset($this->items[$id]);
      TItem::DeleteItemDir($paths['data']. 'posts'. DIRECTORY_SEPARATOR   . $id . DIRECTORY_SEPARATOR  );
      $this->UpdateArchives();
      $this->unlock();
    }
    $this->Deleted($post->id);
    $this->Changed();
    $urlmap->ClearCache();
    return true;
  }
  
  public function Updated(TPost &$post) {
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
    
    foreach ($this->items as $id => $item) {
      if (isset($item['status']) && ($item['status'] == 'future') && ($item['date'] <= time())) {
        $post = TPost::Instance($id);
        $post->status = 'published';
        $this->Edit($post);
      }
    }
  }

public function getarchivescount() {
if (dbversion) return $this->db->getcount("status = 'published');
return count($this->archives);
}
  
  public function getrecent($count) {
if (dbversion) {
return $this->db->idselect("status = 'published'order by created desc limit $count");
} 
    return array_slice(array_keys($this->archives), 0, $count);
}
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
  
  public function StripDrafts(array &$items) {
    return array_intersect($items, array_keys($this->archives));
  }
  
  public function SortAsArchive(array $items) {
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