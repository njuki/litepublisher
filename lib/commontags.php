<?php

class TCommonTags extends TItems implements  ITemplate {
  public $contents;
  //public $sortname;
  //public $showcount;
  public $PermalinkIndex;
  public $PostPropname;
  protected $id;
  private $newname;
  
  protected function create() {
    parent::create();
    //$this->AddEvents();
    $this->data['lite'] = false;
    $this->data['sortname'] = 'count';
    $this->data['showcount'] = true;
    $this->data['maxcount'] =0;
    
    $this->PermalinkIndex = 'category';
    $this->PostPropname = 'categories';
    $this->contents = new TTagContent($this);
  }

protected function gettableitems() {
global $db;
return $db->prefix . $this->table . 'items';
}

public function getitem($id) {
$result = parent::getitem($id);
if (dbversion && empty($result['url'])) {
$urlmap = turlmap::instance();
$result['url'] = $urlmap->getidurl($result['idurl']);
}
return $result;
}

public function &getitems() {
if (dbversion) {
$db = $this->db;
$table = $this->thistable;
$res = $db->query("select $table.*, $db->urlmap.url from $table, $db->urlmap
where $table.idurl = $db->urlmap.id");
return $res->fetchAll(PDO::FETCH_ASSOC);
} else {
return $this->items;
}
}
  
  public function save() {
    parent::save();
    if (!$this->locked)  {
      twidgets::expired($this);
    }
  }
  
  public function getwidgetcontent($id) {
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
  
  public function postedited($idpost) {
    $post = $tpost::instance($idpost);
      $list = $post->{$this->PostPropname};
if (dbversion) {
$items = implode(', ', $list);
$exclude = $this->db->res2array($this->db->query("select tag from $this->itemstable where post = 'idpost' and not tag in (items)");
$this->getdb->$this->itemstable)->delete("tag in (items)");
$this->db->query("insert into $this->itemstable ($idpost, idtag)
select $this->thistable.id as idtag  from $this->thistable  where idtag in ($items)");
//update count
$poststable = $this->db->prefix . 'posts';
$items = implode(', ', array_merge($list, $exclude));
$this->db->query("update $this->thistable set itemscount = postscount 
(select count($this->itemstable.post) as postscount  from $this->itemstable, $poststable 
where tag in ($items) and post = $poststable.id and $poststable.status = 'published' group by post)
where id in ($items)";
} else {
    $this->lock();
    foreach ($this->items as $id => $Item) {
      $toadd = in_array($id, $list);
      $i = array_search($idpost, $Item['items']);
      if (is_int($i) && !$toadd) {
        array_splice($this->items[$id]['items'], $i, 1);
      }
      if ($toadd && !is_int($i)) {
        $this->items[$id]['items'][] = $idpost;
      }
      
            $publ = $posts->stripdrafts($this->items[$id]['items']);
      $this->items[$id]['itemscount'] = count($publ);
    }
    $this->unlock();
}
  }
  
  public function postdeleted($idpost) {
$idpost = (int) $idpost;
if (dbversion) {
$db = $this->db;
$exclude = $this->db->res2array($this->db->query("select tag from $this->itemstable where post = 'idpost' and not tag in (items)");
if (count($exclude) > 0) {
$this->getdb($this->itemstable)->delete("post = $idpost");
$items = implode(', ', $exclude);
$this->db->query("update $this->thistable set itemscount = postscount 
(select count($this->itemstable.post) as postscount  from $this->itemstable, $db->posts
where tag in ($items) and post = $db->posts.id and $db->posts.status = 'published' group by post)
where id in ($items)";
}
return;
}

    $this->lock();
    foreach ($this->items as $id => $item) {
      $i = array_search($idpost, $this->items[$id]['items']);
      if (is_int($i)) {
        array_splice($this->items[$id]['items'], $i, 1);
        $this->items[$id]['itemscount'] = count($this->items[$id]['items']);
      }
    }
    $this->unlock();
  }
  
  public function add($title, $slug = '') {
    if (empty($title)) return false;
    $id  = $this->IndexOf('title', $title);
    if ($id > 0) return $id;
    $this->newname = $slug == '' ? $title : $slug;
    $urlmap =turlmap::instance();
    $Linkgen = TLinkGenerator::instance();
    $url = $Linkgen->createlink($this, $this->PermalinkIndex );

if (dbversion)  {
$id = $this->db->InsertAssoc(array('title' => $title));
$idurl =         $urlmap->add($url, get_class($this),  $id);
$this->db->setvalue($id, 'idurl', $idurl);
} else {
    $this->lock();
$id = ++$this->autoid;
    $this->items[$id] = array(
    'id' => $id,
'idurl' =>         $urlmap->Add($url, get_class($this),  $this->autoid),
    'url' =>$url,
    'title' => $title,
'icon' => 0,
    'itemscount' => 0,
    'items' => array()
    );
    $this->unlock();
}
    $this->added($this->autoid);
$urlmap->clearcache();
    return $id;
  }
  
 public function edit($id, $title, $url) {
    $item = $this->getitem($id);
    if (($item['title'] == $title) && ($item['url'] == $url)) return;
if ($item['title'] != $title)  {
$item['title'] = $title;
if (dbversion) $this->db->setvalue($id, 'title', $title);
}
      $urlmap = turlmap::instance();
if ($item['url'] != $url) {
        if ($url == '') {
          $url = trim($url, '/');
          $this->newname = $url == '' ? $title : $url;
          $Linkgen = TLinkGenerator::instance();
          $url = $Linkgen->createlink($this, $this->PermalinkIndex, false);
        }

        if ($item['url'] != $url) {
//check unique url
if (($idurl = $urlmap->idfind($url) && ($idurl != $item['idurl'])) {
$url = $linkgen->MakeUnique($url);
}
$urlmap->setidurl($item['idurl'], $url);
      $urlmap->addredir($item['url'], $url);
        $item['url'] = $url;
}

     $this->items[$id] = $item;
      $this->save();
      $urlmap->clearcache();
    }

  public function delete($id) {
global $classes;
if (dbversion) {
$this->getdb($this->itemstable)->delete("tag = $id");
$this->db->iddelete($id);
return;
}
    if (isset($this->items[$id])) {
      $posts = $classes->posts;
      $list = $this->items[$id]['items'];
      foreach ($list as $idpost) {
        $post = $posts->getitem($idpost);
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
      $urlmap = turlmap::instance();
      $urlmap->DeleteClassArg(get_class($this), $id);
      $urlmap->clearcache();
      $this->deleted($id);
    }
$this->contents->delete($id);
  }
  
  public function createnames($list) {
    if (is_string($list)) $list = explode(',', trim($list));
    $result = array();
    $this->lock();
    foreach ($list as $title) {
      $title = TContentFilter::escape($title);
      if ($title == '') continue;
      $result[] = $this->add($title);
    }
    $this->unlock();
    return $result;
  }
  
  public function getnames($list) {
if (dbversion) {
$items = implode(', ', $list);
return $this->db->res2array($this->db->query("select title from $this->thistable where id in ($items)");
}
    $result =array();
    foreach ($list as $id) {
      if (!isset($this->items[$id])) continue;
      $result[] = $this->items[$id]['title'];
    }
    return $result;
  }
  
  public function getlink($id) {
    global $options;
$item = $this->getitem($id);
$icon = '';
if ($item['icon'] > 0) {
$icons = ticons::instance();
$icon = $icons->getlink($item['icon']);
}
      return "<a href=\"$options->url{$item['url']}" title=\"{$item['title']}\">{$icon{$item['title']}</a>";
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
if (!$this->itemexists($id)) return 404;
    $this->id = (int) $id;
$item = $this->getitem((int) $id);
$url = $item['url'];
    if($urlmap->page != 1) $url = rtrim($url, '/') . "/page/$urlmap->page/";
    if ($urlmap->url != $url) $urlmap->redir301($url);
  }
  
  public function AfterTemplated(&$s) {
    $redir = "<?php
    global \$urlmap;
  \$url = '{$this->items[$this->id]['url']}';
    if(\$urlmap->page != 1) \$url = rtrim(\$url, '/') . \"/page/\$urlmap->page/\";
    if (\$urlmap->url != \$url) \$urlmap->redir301(\$url);
    ?>";
    $s = $redir.$s;
  }
  
  public function gettitle() {
if ($this->id == '') return $this->newname;
    return isset($this->items[$this->id]) ? $this->items[$this->id]['name'] : TLocal::$data['default']['categories'];
  }
  
  public function gethead() {
    return '';
  }
  
  public function getkeywords() {
$result = $this->contents->getvalue($this->id, 'keywords');
if ($result == '') $result = $this->title;
    return $result;
  }
  
  public function getdescription() {
$result = $this->contents->getvalue($this->id, 'description');
if ($result == '') $result = $this->title;
    return $result;
  }
  
  public function GetTemplateContent() {
    global $classes, $options, $urlmap;
$result = '';
    if ($this->id == 0) {
      $result .= "<ul>\n";
$result .= $this->GetSortedList($this->sortname, 0);
      $result .= "</ul>\n";
      return $result;
    }
        $result .= $this->contents->getcontent($this->id);
if (dbversion) {
$res = $this->db->query("select post from $this->itemstable where tag = $this->id");
$items = $db->res2array($res);
} else {
    $items= $this->items[$this->id]['items'];
}
    $Posts = $classes->posts;
    $items = $Posts->SortAsArchive($items);
    $TemplatePost = &TTemplatePost::instance();
    if ($this->lite) {
      $postsperpage = 1000;
      $list = array_slice($items, ($urlmap->page - 1) * $postsperpage, $postsperpage);
      $result .= $TemplatePost->LitePrintPosts($list);
$theme = ttheme::instance();
      $result .=$theme->getpages($this->items[$this->id]['url'], $urlmap->page, ceil(count($items)/ $postsperpage));
      return $result;
    } else{
      $list = array_slice($items, ($urlmap->page - 1) * $options->postsperpage, $options->postsperpage);
      $TemplatePost = TTemplatePost::instance();
      $result .= $TemplatePost->PrintPosts($list);
$theme = ttheme::instance();
      $result .=$theme->getpages($this->items[$this->id]['url'], $urlmap->page, ceil(count($items)/ $options->postsperpage));
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

class TTagContent extends TDataClass {
private $owner;

public function __construct(TCommonTags $owner) {
parent::__construct();
$this->owner = $owner;
}

 private function getfilename($id) {
    global $paths;
    return $paths['data'] . $this->owner->basename . DIRECTORY_SEPARATOR . $id . '.php';
  }

public function getitem($id) {
if (!isset($this->items[$id])) {
$item = array(
'description' => '',
'content' => '',
'rawcontent' => ''
);

if (dbversion) {
if ($r = $this->db->getitem($id)) $item = $r;
} else {
tfiler::unserialize($this->getfilename($id), $item);
}
$this->items[$id] = $item;
}
return $this->items[$id];
}

public function setitem($id, $item) {
if (isset($this->items[$id]) && ($this->items[$id] == $item)) return;
$this->items[$id] = $item;
if (dbversion) {
$this->db->updateassoc($item);
} else {
    tfiler::serialize($this->getfilename($id), $item);
}
}

public function edit($id, $content, $description, $keywords) {
      $filter = TContentFilter::instance();
$item =array(
'content' => $filter->GetPostContent($content),
'rawcontent' => $content;
'description' => $description,
'keywords' => $keywords
)
$this->setitem($id, $item);
}

  public function delete($id) {
if (dbversion) {
$this->db->iddelete($id);
} else {
    @unlink($this->getfilename($id));
}
  }
  
public function getvalue($id, $name) {
$item = $this->getitem($id);
return $item[$name];
}

  public function getcontent($id) {
return $this->getvalue($id, 'content');
  }
  
  public function setcontent($id, $content) {
$item = $this->getitem($id);
      $filter = TContentFilter::instance();
    $item['rawcontent'] = $content,
$item['content'] = $filter->GetPostContent($content);
    $item['description'] = TContentFilter::GetExcerpt($content, 80);
$this->setitem($id, $item);
  }
  
  public function getdescription() {
return $this->getvalue($id, 'description');
  }

public function getkeywords($id) {
return $this->getvalue($id, 'keywords');
}
  
  }//class

?>