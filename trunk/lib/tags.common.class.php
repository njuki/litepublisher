<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tcommontags extends titems implements  itemplate {
  public $contents;
  public $itemsposts;
  public $PermalinkIndex;
  public $PostPropname;
  public $id;
  private $newtitle;
  
  protected function create() {
    $this->dbversion = dbversion;
    parent::create();
    $this->data['lite'] = false;
    $this->data['includechilds'] = false;
    $this->data['includeparents'] = false;
    $this->PermalinkIndex = 'category';
    $this->PostPropname = 'categories';
    $this->contents = new ttagcontent($this);
    if (!$this->dbversion)  $this->data['itemsposts'] = array();
    $this->itemsposts = new titemspostsowner ($this);
  }
  
  protected function getpost($id) {
    return tpost::instance($id);
  }
  
  public function select($where, $limit) {
    if (!$this->dbversion) $this->error('Select method must be called ffrom database version');
    if ($where != '') $where .= ' and ';
    $db = litepublisher::$db;
    $table = $this->thistable;
    $res = $db->query("select $table.*, $db->urlmap.url from $table, $db->urlmap
    where $where $table.idurl = $db->urlmap.id $limit");
    return $this->res2items($res);
  }
  
  public function load() {
    if (parent::load() && !$this->dbversion) {
      $this->itemsposts->items = &$this->data['itemsposts'];
    }
  }
  
  public function getsortedcontent($parent, $tml, $subtml, $sortname, $count, $showcount) {
    $sorted = $this->getsorted($parent, $sortname, $count);
    if (count($sorted) == 0) return '';
    $result = '';
    $iconenabled = ! litepublisher::$options->icondisabled;
    $theme = ttheme::instance();
    $args = targs::instance();
    $args->rel = $this->PermalinkIndex;
    foreach($sorted as $id) {
      $item = $this->getitem($id);
      $args->add($item);
      $args->icon = $iconenabled ? $this->geticonlink($id) : '';
      $subitems = '';
      if ($showcount) $subitems = sprintf(' (%d)', $item['itemscount']);
      if ($subtml != '') $subitems .= $this->getsortedcontent($id, $tml, $subtml, $sortname, $count, $showcount);
      $args->subitems = $subitems;
      $result .= $theme->parsearg($tml,$args);
    }
    if ($parent == 0) return $result;
    $args->parent = $parent;
    $args->item = $result;
    return $theme->parsearg($subtml, $args);
  }
  
  public function geticonlink($id) {
    $item = $this->getitem($id);
    if ($item['icon'] == 0)  return '';
    $files = tfiles::instance();
    if ($files->itemexists($item['icon'])) return $files->geticon($item['icon'], $item['title']);
    $this->setvalue($id, 'icon', 0);
    if (!$this->dbversion) $this->save();
    return '';
  }
  
  public function geticon() {
    $item = $this->getitem($this->id);
    return $item['icon'];
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
    if (count($items) == 0) return;
    if ($this->dbversion) {
      $db = litepublisher::$db;
      // вначале один запрос к таблице постов, чтобы получить массив новых значений
      //следующие запросы обновляют значение в таблице тегов
      $items = implode(',', $items);
      $thistable = $this->thistable;
      $itemstable = $this->itemsposts->thistable;
      $poststable = $db->posts;
      $list = $db->res2assoc($db->query("select $itemstable.item as id, count($itemstable.item)as itemscount from $itemstable, $poststable
      where $itemstable.item in ($items)  and $itemstable.post = $poststable.id and $poststable.status = 'published'
      group by $itemstable.item"));
      
      $db->table = $this->table;
      foreach ($list as $item) {
        $db->setvalue($item['id'], 'itemscount', $item['itemscount']);
      }
    } else {
      $this->lock();
      foreach ($items as $id) {
        $this->items[$id]['itemscount'] = $this->itemsposts->getpostscount($id);
      }
      $this->unlock();
    }
  }
  
  public function add($parent, $title) {
    if (empty($title)) return false;
    if ($id  = $this->IndexOf('title', $title)) return $id;
    $parent = (int) $parent;
    if (($parent != 0) && !$this->itemexists($parent)) $parent = 0;
    
    $urlmap =turlmap::instance();
    $linkgen = tlinkgenerator::instance();
    $url = $linkgen->createurl($title, $this->PermalinkIndex, true);
    
    $views = tviews::instance();
    $idview = isset($views->defaults[$this->PermalinkIndex]) ? $views->defaults[$this->PermalinkIndex] : 1;
    
    if ($this->dbversion)  {
      $id = $this->db->add(array(
      'parent' => $parent,
      'title' => $title,
      'idview' => $idview
      ));
      $idurl =         $urlmap->add($url, get_class($this),  $id);
      $this->db->setvalue($id, 'idurl', $idurl);
    } else {
      $id = ++$this->autoid;
      $idurl =         $urlmap->add($url, get_class($this),  $id);
    }
    
    $this->lock();
    $this->items[$id] = array(
    'id' => $id,
    'parent' => $parent,
    'idurl' =>         $idurl,
    'url' =>$url,
    'title' => $title,
    'icon' => 0,
    'idview' => $idview,
    'itemscount' => 0
    );
    $this->unlock();
    
    $this->added($this->autoid);
    $urlmap->clearcache();
    return $id;
  }
  
  public function edit($id, $title, $url) {
    $item = $this->getitem($id);
    if (($item['title'] == $title) && ($item['url'] == $url)) return;
    $item['title'] = $title;
    if ($this->dbversion) {
      $this->db->updateassoc(array(
      'id' => $id,
      'title' => $title
      ));
    }
    
    $urlmap = turlmap::instance();
    $linkgen = tlinkgenerator::instance();
    $url = trim($url);
    // try rebuild url
    if ($url == '') {
      $url = $linkgen->createurl($title, $this->PermalinkIndex, false);
    }
    
    if ($item['url'] != $url) {
      if (($urlitem = $urlmap->finditem($url)) && ($urlitem['id'] != $item['idurl'])) {
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
      $title = tcontentfilter::escape($title);
      if ($title == '') continue;
      $result[] = $this->add(0, $title);
    }
    $this->unlock();
    return $result;
  }
  
  public function getnames(array $list) {
    $this->loaditems($list);
    $result =array();
    foreach ($list as $id) {
      if (!isset($this->items[$id])) continue;
      $result[] = $this->items[$id]['title'];
    }
    return $result;
  }
  
  public function getlinks(array $list) {
    if (count($list) == 0) return array();
    $this->loaditems($list);
    $result =array();
    foreach ($list as $id) {
      if (!isset($this->items[$id])) continue;
      $item = $this->items[$id];
      $result[] = sprintf('<a href="%1$s" title="%2$s">%2$s</a>', litepublisher::$site->url . $item['url'], $item['title']);
    }
    return $result;
  }
  
  public function getsorted($parent, $sortname, $count) {
    $count = (int) $count;
    if ($sortname == 'count') $sortname = 'itemscount';
    if (!in_array($sortname, array('title', 'itemscount', 'id'))) $sortname = 'title';
    
    if ($this->dbversion) {
      $limit  = $sortname == 'itemscount' ? "order by $this->thistable.itemscount desc" :"order by $this->thistable.$sortname asc";
      if ($count > 0) $limit .= " limit $count";
      return $this->select("$this->thistable.parent = $parent", $limit);
    }
    
    $list = array();
    foreach($this->items as $id => $item) {
      if ($parent != $item['parent']) continue;
      $list[$id] = $item[$sortname];
    }
    if (($sortname == 'itemscount')) {
      arsort($list);
    } else {
      asort($list);
    }
    
    if (($count > 0) && ($count < count($list))) {
      $list = array_slice($list, 0, $count, true);
    }
    
    return array_keys($list);
  }
  
  //Itemplate
  public function request($id) {
    $this->id = (int) $id;
    try {
      $item = $this->getitem((int) $id);
    } catch (Exception $e) {
      return 404;
    }
    
    $url = $item['url'];
    if(litepublisher::$urlmap->page != 1) $url = rtrim($url, '/') . '/page/'. litepublisher::$urlmap->page . '/';
    if (litepublisher::$urlmap->url != $url) litepublisher::$urlmap->redir301($url);
  }
  
  public function AfterTemplated(&$s) {
    $redir = "<?php
  \$url = '{$this->items[$this->id]['url']}';
    if(litepublisher::\$urlmap->page != 1) \$url = rtrim(\$url, '/') . \"/page/\$urlmap->page/\";
    if (litepublisher::\$urlmap->url != \$url) litepublisher::\$urlmap->redir301(\$url);
    ?>";
    $s = $redir.$s;
  }
  
  public function getname($id) {
    $item = $this->getitem($id);
    return $item['title'];
  }
  
  public function gettitle() {
    $item = $this->getitem($this->id);
    return $item['title'];
  }
  
  public function gethead() {
    return sprintf('<link rel="alternate" type="application/rss+xml" title="%s" href="$site.url/rss/%s/%d.xml" />',
    $this->gettitle(), $this->PostPropname, $this->id);
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
  
  public function getidview() {
    $item = $this->getitem($this->id);
    return $item['idview'];
  }
  
  public function setidview($id) {
    if ($id != $this->idview) {
      $this->setvalue($this->id, 'idview', $id);
    }
  }
  
  public function getcont() {
    $result = '';
    $theme = ttheme::instance();
    if ($this->id == 0) {
      $items = $this->getsortedcontent(0, '<li><a href="$link" title="$title">$icon$title</a>$count</li>',
      '<ul>$item</ul>',
      'count', 0, 0, false);
      $result .= sprintf('<ul>%s</ul>', $items);
      return $result;
    }
    
    $result .= $this->contents->getcontent($this->id);
    if ($result != '') $result = $theme->simple($result);
    
    $items = $this->itemsposts->getposts($this->id);
if ($this->includeparents) {
$parents = $this->getparents($this->id);
foreach ($parents as $id) {
    $items = array_merge($items, array_diff($this->itemsposts->getposts(id), $items));
}
}

if ($this->includechilds) {
$childs = $this->getchillds($this->id);
foreach ($chlds as $id) {
    $items = array_merge($items, array_diff($this->itemsposts->getposts(id), $items));
}
}

    $posts = litepublisher::$classes->posts;
    $items = $posts->stripdrafts($items);
    $items = $posts->sortbyposted($items);
    $perpage = $this->lite ? 1000 : litepublisher::$options->perpage;
    $list = array_slice($items, (litepublisher::$urlmap->page - 1) * $perpage, $perpage);
    $result .= $theme->getposts($list, $this->lite);
    $item = $this->getitem($this->id);
    $result .=$theme->getpages($item['url'], litepublisher::$urlmap->page, ceil(count($items)/ $perpage));
    return $result;
  }
  
  public function Setlite($lite) {
    if ($lite != $this->lite) {
      $this->data['lite'] = $lite;
      $this->save();
    }
  }

public function getparents($id) {
$result = array();
while ($id = (int) $this->items[$id]['parent']) $result[] = $id;
return $result;
}

public function getchilds($parent) {
$result = array();
foreach ($this->items as $id => $item) {
if ($parent == $item['parent']) {
$result[] =$id;
$result = array_merge($result, $this->getchilds($id));
}
}
return $result;
}

}//class

class ttagcontent extends tdata {
  private $owner;
  private $items;
  
  public function __construct(TCommonTags $owner) {
    parent::__construct();
    $this->owner = $owner;
    $this->items = array();
  }
  
  private function getfilename($id) {
    return litepublisher::$paths->data . $this->owner->basename . DIRECTORY_SEPARATOR . $id . '.php';
  }
  
  public function getitem($id) {
    if (isset($this->items[$id]))  return $this->items[$id];
    $item = array(
    'description' => '',
    'keywords' => '',
    'content' => '',
    'rawcontent' => ''
    );
    
    if ($this->owner->dbversion) {
      if ($r = $this->db->getitem($id)) $item = $r;
    } else {
      tfiler::unserialize($this->getfilename($id), $item);
    }
    $this->items[$id] = $item;
    return $item;
  }
  
  public function setitem($id, $item) {
    if (isset($this->items[$id]) && ($this->items[$id] == $item)) return;
    $this->items[$id] = $item;
    if ($this->owner->dbversion) {
      $item['id'] = $id;
      $this->db->insert($item);
    } else {
      tfiler::serialize($this->getfilename($id), $item);
    }
  }
  
  public function edit($id, $content, $description, $keywords) {
    $item = $this->getitem($id);
    $filter = tcontentfilter::instance();
    $item =array(
    'content' => $filter->filter($content),
    'rawcontent' => $content,
    'description' => $description,
    'keywords' => $keywords
    );
    $this->setitem($id, $item);
  }
  
  public function delete($id) {
    if ($this->owner->dbversion) {
      $this->db->iddelete($id);
    } else {
      @unlink($this->getfilename($id));
    }
  }
  
  public function getvalue($id, $name) {
    $item = $this->getitem($id);
    return $item[$name];
  }
  
  public function setvalue($id, $name, $value) {
    $item = $this->getitem($id);
    $item[$name] = $value;
    $this->setitem($id, $item);
  }
  
  public function getcontent($id) {
    return $this->getvalue($id, 'content');
  }
  
  public function setcontent($id, $content) {
    $item = $this->getitem($id);
    $filter = tcontentfilter::instance();
    $item['rawcontent'] = $content;
    $item['content'] = $filter->filter($content);
    $item['description'] = tcontentfilter::getexcerpt($content, 80);
    $this->setitem($id, $item);
  }
  
  public function getdescription($id) {
    return $this->getvalue($id, 'description');
  }
  
  public function getkeywords($id) {
    return $this->getvalue($id, 'keywords');
  }
  
}//class

class tcommontagswidget extends twidget {
  
  protected function create() {
    parent::create();
    $this->adminclass = 'tadmintagswidget';
    $this->data['sortname'] = 'count';
    $this->data['showcount'] = true;
    $this->data['showsubitems'] = true;
    $this->data['maxcount'] =0;
  }
  
  public function getowner() {
    return false;
  }
  
  public function getcontent($id, $sidebar) {
    $theme = ttheme::instance();
    $items = $this->owner->getsortedcontent(0,
    $theme->getwidgetitem($this->template, $sidebar),
    $this->showsubitems ? $theme->getwidgettml($sidebar, $this->template, 'subitems') : '',
    $this->sortname, $this->maxcount, $this->showcount);
    return $theme->getwidgetcontent($items, $this->template, $sidebar);
  }
  
}//class
?>