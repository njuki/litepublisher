<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class tcommontags extends TItems implements  ITemplate {
  public $contents;
public $itemsposts;
  public $PermalinkIndex;
  public $PostPropname;
  protected $id;
  private $newname;
  
  protected function create() {
    parent::create();
$this->data['itemsposts'] = array();
    $this->PermalinkIndex = 'category';
    $this->PostPropname = 'categories';
    $this->contents = new ttagcontent($this);
$this->itemsposts = new titemspostsowner ($this);
  }

protected function getpost($id) {
return tpost::instance($id);
}

public function getitem($id) {
$result = parent::getitem($id);
if (dbversion && empty($result['url'])) {
$urlmap = turlmap::instance();
$result['url'] = $urlmap->getidurl($result['idurl']);
}
return $result;
}

public function loaditems(array $items) {
global  $db;
if (!dbversion) return;
//исключить из загрузки загруженные посты
$list = implode(',', array_diff($items, array_keys($this->items)));
$table = $this->thistable;
$res = $db->query("select $table.*, $db->urlmap.url as url  from $table, $db->urlmap
where $table.id in ($list) and  $db->urlmap.id  = $table.idurl");

foreach ($res->fetch(PDO::FETCH_ASSOC) as $item) {
$this->items[$item['id']] = $item;
}
}

public function &getallitems() {
global $db;
if (dbversion) {
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
$thisoptions = $this->options;
return $this->GetSortedList($thisoptions->sortname, $thisoptions->maxcount);
}

private function GetSortedList($sortname, $count) {
    global $options;
    $result = '';
$theme = ttheme::instance();
$tml = $theme->getwidgetitem('tag');
$showcount = $this->options->showcount;
        $Sorted = $this->getsorted($sortname, $count);
    foreach($Sorted as $item) {
$count = $showcount ? " ({$item['itemscount']}" : '';
  $result .= sprintf($tml, $options->url . $item['url'], $item['title'], $count);
    }
    return $result;
  }

public function geturl($id) {
$item = $this->getitem($id);
return $item['url'];
}
  
  public function postedited($idpost) {
    $post = $this->getpost((int) $idpost);
$this->lock();
$changed = $this->itemsposts->setitems($idpost, $post->{$this->PostPropname});
$this->updatecount($changed);
$this->unlock();
  }
  
  public function postdeleted($idpost) {
$this->lock();
$changed = $this->itemsposts->deletepost($idpost);
$this->updatecount($changed);
$this->unlock();
  }

private function updatecount(array $items) {
if (dbversion) {
$items = implode(',', $items);
$thistable = $this->thistable;
$itemstable = $this->itemsposts->thistable;
$poststable = $this->db->posts;
$this->db->query("
update $thistable, $itemstable set $thistable.itemscount = 
(select count(post)from $itemstable, $poststable
 where item in ($all)  and post = $poststable.id and $poststable.status = 'published' group by post)
$thistable.id = $itemstable.item");
} else {
$this->lock();
foreach ($items as $id) {
      $this->items[$id]['itemscount'] = $this->itemsposts->getpostscount($id);
}
    $this->unlock();
}
}
  
  public function add($title, $slug = '') {
    if (empty($title)) return false;
    $id  = $this->IndexOf('title', $title);
    if ($id > 0) return $id;
    $this->newname = $slug == '' ? $title : $slug;
    $urlmap =turlmap::instance();
    $linkgen = tlinkgenerator::instance();
    $url = $linkgen->createlink($this, $this->PermalinkIndex );

if (dbversion)  {
$id = $this->db->add(array('title' => $title));
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
          $linkgen = tlinkgenerator::instance();
          $url = $linkgen->createlink($this, $this->PermalinkIndex, false);
        }
}

        if ($item['url'] != $url) {
//check unique url
if (($idurl = $urlmap->idfind($url)) && ($idurl != $item['idurl'])) {
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
$item = $this->getitem($id);
      $urlmap = turlmap::instance();
      $urlmap->deleteitem($item['idurl']);

$this->lock();
$this->contents->delete($id);
$list = $this->itemsposts->getposts($id);
$this->itemsposts->deleteitem($id);
parent::delete($id);
$this->unlock();
$this->itemsposts->updateposts($list, $this->PostPropname);
     $urlmap->clearcache();
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
$items = implode(',', $list);
return $this->db->res2array($this->db->query("select title from $this->thistable where id in ($items)"));
}
    $result =array();
    foreach ($list as $id) {
      if (!isset($this->items[$id])) continue;
      $result[] = $this->items[$id]['title'];
    }
    return $result;
  }
  
  public function getsorted($sortname, $count) {
$count = (int) $count;
if (!in_array($sortname, array('title', 'count', 'id'))) $sortname = 'title';

if (dbversion) {
$table = $this->thistable;
$q = "select $table.*, $this->urltable.url from $table, $this->urltable
where $table.parent = 0 and $this->urltable.id= $table.idurl sort by ";
$q .= $sortname == 'count' ? "itemscount asc" :"$sortname desc";
if ($count > 0) $q .= " limit $count";
$res = $this->db->query($q);
return $this->res2array($res);
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
$result .= $this->GetSortedList($this->options->sortname, 0);
return sprintf("<ul>\n%s</ul>\n", $result);
    }
        $result .= $this->contents->getcontent($this->id);

if (dbversion) {
$res = $this->db->query("select post from $this->itemstable where tag = $this->id");
$items = $db->res2array($res);
} else {
    $items= $this->items[$this->id]['items'];
}
    $Posts = $classes->posts;
    $items = $Posts->sortbyposted($items);

$lite = $this->options->lite;
      $postsperpage = $lite ? 1000 : $options->postsperpage;
      $list = array_slice($items, ($urlmap->page - 1) * $postsperpage, $postsperpage);
$theme = ttheme::instance();
      $result .= $theme->getposts($list, $lite);
$item = $this->getitem($this->id);
      $result .=$theme->getpages($item['url'], $urlmap->page, ceil(count($items)/ $postsperpage));
      return $result;
  }
  
  public function SetParams($lite, $sortname, $showcount, $maxcount) {
$this->options = array(
'lite' => $lite,
'sortname' => $sortname,
'showcount' => $showcount,
'maxcount' => (int) $maxcount
);
  }
  
}//class

class ttagcontent extends tdata {
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
'keywords' => '',
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
'content' => $filter->filter($content),
'rawcontent' => $content,
'description' => $description,
'keywords' => $keywords
);
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
      $filter = tcontentfilter::instance();
    $item['rawcontent'] = $content;
$item['content'] = $filter->GetPostContent($content);
    $item['description'] = tcontentfilter::getexcerpt($content, 80);
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