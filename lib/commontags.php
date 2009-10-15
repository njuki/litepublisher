<?php

class TCommonTags extends TItems implements  ITemplate {
  //public $sortname;
  //public $showcount;
  public $PermalinkIndex;
  public $PostPropname;
  protected $id;
  private $NewName;
  
  protected function create() {
    parent::create();
    //$this->AddEvents();
    $this->data['lite'] = false;
    $this->data['sortname'] = 'count';
    $this->data['showcount'] = true;
    $this->data['maxcount'] =0;
    
    $this->PermalinkIndex = 'category';
    $this->PostPropname = 'categories';
  }

protected function gettableitems() {
global $db;
return $db->prefix . $this->table . 'items';
}
  
  public function save() {
    parent::save();
    if (!$this->locked)  {
      ttemplate::WidgetExpired($this);
    }
  }
  
  public function GetWidgetContent($id) {
return $this->GetSortedList($this->sortname, $this->maxcount);
}

private function GetSortedList($sortname, $count) {
    global $options;
    $result = '';
$templ = "<li><a href=\"$options->url%1\$s\" title=\"%2\$s\">%2\$s</a>";
      if ($this->showcount) $templ .= ' (%3$s)';
$templ .= "</li>\n";

        $Sorted = $this->getsorted($sortname, $count);
    foreach($Sorted as $item) {
  $result .= sprintf($item['url'], $item['title'], $item['itemscount']);
    }
    return $result;
  }

public function geturl($id) {
if (!isset($this->items[$id]) && dbversion) {
$res = $this->db->query("select $this->thistable.*, $this->urltable.url  from $this->thistable 
$this->joinurl where $this->thistable.id = $id  limit 1");
$this->items[$id] = $res->fetch(PDO::FETCH_ASSOC);
} else {

return $this->items[$id]['url'];
}
  
  public function PostEdit($postid) {
    $post = $tpost::instance($postid);
      $list = $post->{$this->PostPropname};
if (dbversion) {
$items = implode(', ', $list);
$this->getdb->$this->itemstable)->deletetag in (items)");
$this->db->query("insert into $this->itemstable ($postid, idtag)
select $this->thistable.id as idtag  from $this->thistable  where idtag in ($items)");
$this->db->query("update 
select m.id,m.c,count(*) from m left join s using(id) group by m.id; 
update $this->thistable (itemscount = pcount)

select post from $this->itemstable  where  tag in (items)

where id in (list)";
} else {
    $this->lock();
    foreach ($this->items as $id => $Item) {
      $toadd = in_array($id, $list);
      $i = array_search($postid, $Item['items']);
      if (is_int($i) && !$toadd) {
        array_splice($this->items[$id]['items'], $i, 1);
      }
      if ($toadd && !is_int($i)) {
        $this->items[$id]['items'][] = $postid;
      }
      
            $publ = $posts->stripdrafts($this->items[$id]['items']);
      $this->items[$id]['itemscount'] = count($publ);
    }
    $this->unlock();
}
  }
  
  public function PostDeleted($postid) {
$postid = (int) $postid;
if (dbversion) {
$this->getdb($this->itemstable)->delete("post = $postid");
//$this->db->
return;
}

    $this->lock();
    foreach ($this->items as $id => $item) {
      $i = array_search($postid, $this->items[$id]['items']);
      if (is_int($i)) {
        array_splice($this->items[$id]['items'], $i, 1);
        $this->items[$id]['itemscount'] = count($this->items[$id]['items']);
      }
    }
    $this->unlock();
  }
  
  //for link generator
  public function name() {
    return $this->NewName;
  }
  
  public function Add($name, $slug = '') {
    if (empty($name)) return false;
    $id  = $this->IndexOf('name', $name);
    if ($id > 0) return $id;
    $this->lock();
    $this->lastid++;
    $this->NewName = $slug == '' ? $name : $slug;
    $Linkgen = &TLinkGenerator::instance();
    $url = $Linkgen->Create($this, $this->PermalinkIndex );
    $this->items[$this->lastid] = array(
    'id' => $this->lastid,
    'count' => 0,
    'name' => $name,
    'url' =>$url,
    //'description ' => '',
    //'keywords' => '',
    'items' => array()
    );
    $this->unlock();
    $this->AddUrl($this->lastid, $url);
    $this->Added($this->lastid);
    return $this->lastid;
  }
  
  public function AddUrl($id, $url) {
    $urlmap =turlmap::instance();
    $dir = "/$this->PermalinkIndex/";
    if (substr($url, 0, strlen($dir)) == $dir) {
      $subdir = substr($url, strlen($dir));
      $subdir = trim($subdir, '/');
      if (strpos($subdir, '/')) {
        $urlmap->Add($url, get_class($this),  $id);
      } else {
        $urlmap->AddSubNode($this->PermalinkIndex, $subdir, get_class($this), $id);
      }
    } else {
      $urlmap->Add($url, get_class($this),  $id);
    }
    $urlmap->ClearCache();
  }
  
  public function Edit($id, $name, $url) {
    $item = $this->items[$id];
    if (($item['name'] != $name) || ($item['url'] != $url)) {
      $urlmap = &turlmap::instance();
      $urlmap->lock();
      
      $this->lock();
      $item['name'] = $name;
      if ($item['url'] != $url) {
        $urlmap->DeleteClassArg(get_class($this), $id);
        if ($url == '') {
          $url = trim($url, '/');
          $this->NewName = $url == '' ? $name : $url;
          $Linkgen = &TLinkGenerator::instance();
          $url = $Linkgen->Create($this, $this->PermalinkIndex );
        }
        $this->AddUrl($id, $url);
        if ($item['url'] != $url) {
          $urlmap->AddRedir($item['url'], $url);
        }
        
        $item['url'] = $url;
      }
      
      $this->items[$id] = $item;
      $this->unlock();
      $urlmap->ClearCache();
      $urlmap->unlock();
    }
  }
  
  public function Delete($id) {
    if (isset($this->items[$id])) {
      $posts = getnamedinstance('posts', 'tposts');
      $list = $this->items[$id]['items'];
      foreach ($list as $idpost) {
        $post = &$posts->GetItem($idpost);
      $postcats = $post->{$this->PostPropname};
        $i = array_search($id, $postcats);
        if (is_int($i)) {
          array_splice($postcats, $i, 1);
        $post->{$this->PostPropname} = $postcats;
          $post->Save();
        }
      }
      unset($this->items[$id]);
      $this->Save();
      $urlmap = &turlmap::instance();
      $urlmap->DeleteClassArg(get_class($this), $id);
      $urlmap->ClearCache();
      $this->Deleted($id);
    }
  }
  
  public function CreateNames($list) {
    if (is_string($list)) $list = explode(',', trim($list));
    $result = array();
    $this->lock();
    foreach ($list as $name) {
      $name = TContentFilter::escape($name);
      if ($name == '') continue;
      $result[] = $this->Add($name);
    }
    $this->unlock();
    return $result;
  }
  
  public function GetNames($list) {
    $result =array();
    foreach ($list as $id) {
      if (!isset($this->items[$id])) continue;
      $result[] = $this->items[$id]['name'];
    }
    return $result;
  }
  
  public function GetLink($id) {
    global $options;
    if ($this->ItemExists($id)) {
      return '<a href="'. $options->url . $this->items[$id]['url'] . '">' . $this->items[$id]['name'] . '</a>';
    } else {
      return '';
    }
  }
  
  public function getsorted($sortname, $count) {
$count = (int) $count;
if (!in_array($sortname, array('title', 'count', 'id')) $sortname = 'name';
if (dbversion) {
$q = "select $this->thistable.*, $this->urltable.url from $this->thistable
$this->joinurl where parent = 0 sort by ";
$q .= $sortname == 'count' "itemscount asc" :"$sortname desc";
if ($count > 0) $q .= " limit $count";
$res = $this->db->query($q);
return $res->fetchAll(PDO::FETCH_ASSOC);
}

    $list = array();
    foreach($this->items as $id => $item) {
      $list[$id] = $item[$sortname];
    }
    if (($sortname == 'count')) {
      arsort($list);
    } else {
      asort($list);
    }

    if (($count > 0) && ($count < count($list))) {
      $list = array_slice($list, 0, $count, true);
    }

$result = array();
foreach($list as $id => $val) {
$result[] = $this->items[$id];
}
    return $result;
  }
  
  //Itemplate
  public function request($id) {
    global $urlmap;
    $this->id = $id;
    if (!isset($this->items[$id])) return 404;
    $url = $this->items[$this->id]['url'];
    if($urlmap->pagenumber != 1) $url = rtrim($url, '/') . "/page/$urlmap->pagenumber/";
    if ($urlmap->url != $url) $urlmap->Redir301($url);
  }
  
  public function AfterTemplated(&$s) {
    $redir = "<?php
    global \$urlmap;
  \$url = '{$this->items[$this->id]['url']}';
    if(\$urlmap->pagenumber != 1) \$url = rtrim(\$url, '/') . \"/page/\$urlmap->pagenumber/\";
    if (\$urlmap->url != \$url) \$urlmap->Redir301(\$url);
    ?>";
    $s = $redir.$s;
  }
  
  public function gettitle() {
    return isset($this->items[$this->id]) ? $this->items[$this->id]['name'] : TLocal::$data['default']['categories'];
  }
  
  public function gethead() {
    return '';
  }
  
  public function getkeywords() {
    return $this->title;
  }
  
  public function getdescription() {
    return '';
  }
  
  public function GetTemplateContent() {
    global $options, $urlmap;
    $result = '';
    if ($this->id == 0) {
      $result .= "<ul>\n";
$result .= $this->GetSortedList($this->sortname, 0);
      $result .= "</ul>\n";
      return $result;
    }
    
if (dbversion) {
$res = $this->db->query("select post from $this->itemstable where tag = $this->id");
$items = $db->res2array($res);
} else {
    $items= $this->items[$this->id]['items'];
}
    $Posts = getnamedinstance('posts', 'tposts');
    $items = $Posts->SortAsArchive($items);
    $TemplatePost = &TTemplatePost::instance();
    if ($this->lite) {
      $postsperpage = 1000;
      $list = array_slice($items, ($urlmap->pagenumber - 1) * $postsperpage, $postsperpage);
      $result .= $TemplatePost->LitePrintPosts($list);
      $result .=$TemplatePost->PrintNaviPages($this->items[$this->id]['url'], $urlmap->pagenumber, ceil(count($items)/ $postsperpage));
      return $result;
    } else{
      $list = array_slice($items, ($urlmap->pagenumber - 1) * $options->postsperpage, $options->postsperpage);
      $TemplatePost = TTemplatePost::instance();
      $result .= $TemplatePost->PrintPosts($list);
      $result .=$TemplatePost->PrintNaviPages($this->items[$this->id]['url'], $urlmap->pagenumber, ceil(count($items)/ $options->postsperpage));
      return $result;
    }
  }
  
  public function GetParams() {
    return array(
    'lite' => $this->lite,
    'sortname' => $this->sortname,
    'showcount' => $this->showcount,
    'maxcount' => $this->maxcount
    );
  }
  
  public function SetParams($lite, $sortname, $showcount, $maxcount) {
    if (($lite != $this->lite) || ($sortname != $this->sortname) || ($showcount != $this->showcount) || ($maxcount != $this->maxcount)) {
      $this->lite = $lite;
      $this->sortname = $sortname;
      $this->showcount = $showcount;
      $this->maxcount = $maxcount;
      $this->save();
    }
  }
  
}//class

?>