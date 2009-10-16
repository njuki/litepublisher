<?php

class tposts extends TItems {
  public $archives;
public $rawtable;

  public static function instance() {
    return GetNamedInstance('posts', __class__);
  }
  
  public static function unsub($obj) {
    $self = self::instance();
    $self->UnsubscribeClassName(get_class($obj));
  }
  
  protected function create() {
    parent::create();
$this->table = 'posts';
$this->rawtable = 'postsraw';
    $this->basename = 'posts'  . DIRECTORY_SEPARATOR  . 'index';
    $this->addevents('edited', changed', 'singlecron');
    if (!dbversion) $this->AddDataMap('archives' , array());
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
  
 public function GetWidgetContent($id) {
    global $options;
    $template = template::instance();
    $item = !empty($template->theme['widget']['recentpost']) ? $template->theme['widget']['recentpost'] :
    '<li><strong><a href=\'$options->url$post->url\' rel=\'bookmark\' title=\'Permalink to $post->title\'>$post->title</a></strong><br />
    <small>$post->localdate</small></li>';
    
    $result = '';
    $list = $this->getrecent($options->recentcount);
    foreach ($list as $id) {
      $post = tpost::instance($id);
      eval('$result .= "'. $item . '\n";');
    }
    $result = str_replace("'", '"', $result);
    return $result;
  }

public function load() {
if (dbversion) return true;
return parent::load();
}

public function save() {
if (dbversion) return true;
return parent::save();
}

    public function add(tpost $post) {
    if ($post->date == 0) $post->date = time();
    $post->modified = time();
      $post->pagescount = count($post->pages);
    
    $Linkgen = TLinkGenerator::instance();
    if ($post->url == '' ) {
      $post->url = $Linkgen->createlink($post, 'post');
    } else {
      $title = $post->title;
      $post->title = trim($post->url, '/');
      $post->url = $Linkgen ->createlink($post, 'post');
      $Post->title = $title;
    }

        $urlmap = turlmap::instance();
    if (dbversion) {
      $post->id = TPostTransform ::add($post);
      $post->idurl = $urlmap->add($post->url, get_class($post), $post->id);      
$post->db->setvalue($post->id, 'idurl', $post->idurl);
$post->rawdb->InsertAssoc(array('id' => $post->id, 'rawcontent' => $post->data['rawcontent']));
      
     foreach ($post->pages as $i => $content) {
        $this->getdb('pages')->InsertAssoc(array('post' => $post->id, 'page' => $i         'content' => $content));
      }
   } else {
      global $paths;
      $post->id = ++$this->lastid;
      $dir =$paths['data'] . 'posts' . DIRECTORY_SEPARATOR  . $post->id;
      @mkdir($dir, 0777);
      @chmod($dir, 0777);
    $post->idurl = $urlmap->Add($post->url, get_class($post), $post->id);
}      
      $this->lock();
      $this->Updated($post);
      $post->save();
      $this->unlock();
    $this->Added($post->id);
    $this->Changed();
    $urlmap->ClearCache();
    return $post->id;
  }
  
  public function edit(tpost $post) {
    $post->modified = time();
    $urlmap = turlmap::instance();
        $oldurl = $urlmap->gitidurl($post->idurl);
    if ($oldurl != $post->url) {
      $Linkgen = TLinkGenerator::instance();
      if ($post->url == '') {
        $post->url = $Linkgen->createlink($post, 'post', false);
      } else {
        $title = $post->title;
        $post->title = trim($post->url, '/');
        $post->url = $Linkgen->Create($post, 'post', false);
        $post->title = $title;
      }

    if ($oldurl != $post->url) {
//check unique url
if (($idurl = $urlmap->idfind($post->url) && ($idurl != $post->idurl)) {
$post->url = $linkgen->MakeUnique($post->url);
}
$urlmap->setidurl($post->idurl, $post->url);
      $urlmap->addredir($oldurl, $post->url);
    }

    $this->lock();    
    $this->updated($post);
    $post->save();
    $this->unlock();
        $this->edited($post->id);
    $this->changed();
    $urlmap->ClearCache();
  }
  
  public function delete($id) {
global $classes;
    if (!$this->ItemExists($id)) return false;
    $urlmap = turlmap::instance();
$urmap->DeleteClassArg($classes->classes['post'], $id);
    if (dbversion) {
      $this->db->iddelete($id);
      $this->getdb('pages')->delete("post = $id");
$this->getdb($this->rawtable)->iddelete($id);
    } else {
      global $paths;
      TItem::DeleteItemDir($paths['data']. 'posts'. DIRECTORY_SEPARATOR   . $id . DIRECTORY_SEPARATOR  );
      unset($this->items[$id]);
      $this->UpdateArchives();
}
$this->lock();
    $this->PublishFuture();
$this->unlock();
    $this->deleted($post->id);
    $this->changed();
    $urlmap->ClearCache();
    return true;
  }
  
  public function updated(tpost $post) {
    if (($post->status == 'published') && ($post->date > time())) {
      $post->status = 'future';
if (dbversion) $post->db->setvalue($post->id, 'status', 'future');
    }
    $this->PublishFuture();
if (!dbversion) {
    $this->items[$post->id] = array(
    'date' => $post->date
    );
    if   ($post->status != 'published') $this->items[$post->id]['status'] = $post->status;
    $this->UpdateArchives();
}

    $Cron = tcron::instance();
    $Cron->add('single', get_class($this), 'DoSingleCron', $post->id);
  }
  
  public function UpdateArchives() {
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
    $GLOBALS['post'] = tpost::instance($id);
    $this->singlecron($id);
    //ping
  }
  
  public function hourcron() {
    $this->PublishFuture();
  }
  
private function publish($id) {
$post = tpost::instance($id);
        $post->status = 'published';
        $this->edit($post);
}

  public function PublishFuture() {
    if (dbversion) {
if ($list = $this->db->idselect("status = 'future' and created <= now() order by created asc")) {
foreach( $list as $id) $this->publish($id);
}
} else {
    foreach ($this->items as $id => $item) {
      if (isset($item['status']) && ($item['status'] == 'future') && ($item['date'] <= time())) $this->publish($id);
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
  
  public function GetPublishedRange($PageNum, $CountPerPage) {
    $Count = $this->archivescount;
    $From = ($PageNum - 1) * $CountPerPage;
    if ($From > $Count)  return array();
if (dbversion)  return $this->db->idselect("status = 'published' order by created desc from $from limit $CountPerPage");
    $To = min($From + $CountPerPage, $Count);
return array_slice(array_keys($this->archives), $From, $To - $From);
  }
  
  public function StripDrafts(array $items) {
if (dbversion) {
$list = implode(', ', $items);
return $this->db->idselect("status = 'published' and id in ($list)");
} else {
    return array_intersect($items, array_keys($this->archives));
}
  }
  
  public function SortAsArchive(array $items) {
if (dbversion) {
$list = implode(', ', $items);
return $this->db->idselect("status = 'published' and id in ($list) order by created desc");
}

    $result = array();
    foreach ($items as  $id) {
      if (isset($this->archives[$id])) {
        $result[$id] = $this->archives[$id];
      }
    }
    
    arsort($result,  SORT_NUMERIC);
    return array_keys($result);
  }
  
}//class

?>