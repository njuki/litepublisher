<?php

class TMenu extends TItems {
public $tree;
  protected $home;

    public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'menus' . DIRECTORY_SEPARATOR   . 'index';
    $this->addmap('home', array());
$this->addmap('tree', array());
    $this->addevents('edited');
  }
  
  public function add(tmenuitem $item) {
    $Linkgen = TLinkGenerator::instance();
    if ($item->url == '' ) {
      $item->url = $Linkgen->createlink($item, 'post');
    } else {
      $title = $item->title;
      $item->title = trim($post->url, '/');
      $item->url = $Linkgen ->createlink($item, 'post');
      $item->title = $title;
    }

        $urlmap = turlmap::instance();

      $item->id = ++$this->autoid;
    $item->idurl = $urlmap->Add($item->url, get_class($item), $item->id);
}      
      $this->lock();
      $this->updated($item);
      $item->save();
      $this->unlock();
    $this->Added($post->id);
    $urlmap->ClearCache();
    return $item->id;
  }

public function getdir() {
global $paths;
return $paths['data'] . 'menus' . DIRECTORY_SEPARATOR;
}
   
public function edit(tmenuitem $item) {
    $urlmap = turlmap::instance();
        $oldurl = $urlmap->gitidurl($item->idurl);
    if ($oldurl != $item->url) {
      $Linkgen = TLinkGenerator::instance();
      if ($item->url == '') {
        $item->url = $Linkgen->createlink($item, 'item', false);
      } else {
        $title = $item->title;
        $item->title = trim($item->url, '/');
        $item->url = $Linkgen->Create($item, 'item', false);
        $item->title = $title;
      }

    if ($oldurl != $item->url) {
//check unique url
if (($idurl = $urlmap->idfind($item->url) && ($idurl != $item->idurl)) {
$item->url = $linkgen->MakeUnique($item->url);
}
$urlmap->setidurl($item->idurl, $item->url);
      $urlmap->addredir($oldurl, $item->url);
    }

    $this->lock();    
    $this->updated($item);
    $item->save();
    $this->unlock();
        $this->edited($item->id);

    $urlmap->clearcache();
  }

  public function  delete($id) {
    if (!$this->itemexists($id)) return false;
    if ($this->GetChildsCount($id) > 0) return false;

    $urlmap = turlmap::instance();
$urmap->DeleteClassArg($classes->classes['post'], $id);

    $this->lock();
    unset($this->items[$id]);
    $this->Sort();
    $this->unlock();
    $this->deleted($id);
@unlink($this->dir . "$id.php");
@unlink($this->dir . "$id.bak.php");
    $urlmap->clearcache();
    return true;
  }
  
   public function updated(tmenuitem $item) {
    $this->sort();
  }
  
  public function sort() {
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
  
  private function GetChildsCount($id) {
    return count($this->items[$id]['childs']);
  }
  
}//class

class tmenuitem extends Titem {
}
?>