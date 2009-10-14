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
  
  public function save() {
    parent::save();
    if (!$this->locked)  {
      ttemplate::WidgetExpired($this);
    }
  }
  
  public function GetWidgetContent($id) {
    global $options;
    $result = '';
    
    $Sorted = $this->getsorted($this->sortname);
    if (($this->maxcount > 0) && ($this->maxcount < count($Sorted))) {
      $Sorted = array_slice($Sorted, 0, $this->maxcount, true);
    }
    
    foreach($Sorted as $id => $value ) {
$url = $this->geturl($id);


  $result .= "<li><a href=\"$options->url$url\">{$this->items[$id]['name']}</a>";
      if ($this->showcount) $result .= ' ('. $this->items[$id]['count'] . ')';
      $result .= "</li>\n";
    }
    
    return $result;
  }

public function geturl($id) {
if (!isset($this->items[$id]) && dbversion) {
global $db;
$urlmap = turlmap::instance();
$table = $db->prefix . $this->table;
$urltable = $db->prefix . $urlmap->table;
$res = $db->query("select $table.*, $urltable.url  from $table
  left join $urltable on $urltable.id = $table.urlid
where $table.id = $id limit 1");
$this->items[$id] = $res->fetch(PDO::FETCH_ASSOC);
} else {

return $this->items[$id]['url'];
}
  
  public function PostEdit($postid) {
    $posts = getnamedinstance('posts, 'tposts');
    $post = $posts->getitem($postid);
    
  $list = $post->{$this->PostPropname};
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
      $this->items[$id]['count'] = count($publ);
    }
    $this->unlock();
  }
  
  public function PostDeleted($postid) {
    $this->lock();
    foreach ($this->items as $id => $item) {
      $i = array_search($postid, $this->items[$id]['items']);
      if (is_int($i)) {
        array_splice($this->items[$id]['items'], $i, 1);
        $this->items[$id]['count'] = count($this->items[$id]['items']);
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
    $Linkgen = &TLinkGenerator::Instance();
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
    $Urlmap =TUrlmap::Instance();
    $dir = "/$this->PermalinkIndex/";
    if (substr($url, 0, strlen($dir)) == $dir) {
      $subdir = substr($url, strlen($dir));
      $subdir = trim($subdir, '/');
      if (strpos($subdir, '/')) {
        $Urlmap->Add($url, get_class($this),  $id);
      } else {
        $Urlmap->AddSubNode($this->PermalinkIndex, $subdir, get_class($this), $id);
      }
    } else {
      $Urlmap->Add($url, get_class($this),  $id);
    }
    $Urlmap->ClearCache();
  }
  
  public function Edit($id, $name, $url) {
    $item = $this->items[$id];
    if (($item['name'] != $name) || ($item['url'] != $url)) {
      $Urlmap = &TUrlmap::Instance();
      $Urlmap->lock();
      
      $this->lock();
      $item['name'] = $name;
      if ($item['url'] != $url) {
        $Urlmap->DeleteClassArg(get_class($this), $id);
        if ($url == '') {
          $url = trim($url, '/');
          $this->NewName = $url == '' ? $name : $url;
          $Linkgen = &TLinkGenerator::Instance();
          $url = $Linkgen->Create($this, $this->PermalinkIndex );
        }
        $this->AddUrl($id, $url);
        if ($item['url'] != $url) {
          $Urlmap->AddRedir($item['url'], $url);
        }
        
        $item['url'] = $url;
      }
      
      $this->items[$id] = $item;
      $this->unlock();
      $Urlmap->ClearCache();
      $Urlmap->unlock();
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
      $Urlmap = &TUrlmap::Instance();
      $Urlmap->DeleteClassArg(get_class($this), $id);
      $Urlmap->ClearCache();
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
  
  public function getsorted($sortname) {
if (!in_array($sortname, array('title', 'count', 'id')) $sortname = 'name';
if (dbversion) {
if ($sortname == 'count') return $this->db->idselect("parent = 0 sort by itemscount asc");
return $this->db->idselect("parent = 0 sort by $sortname desc");
}

    $result = array();
    foreach($this->items as $id => $item) {
      $result[$id] = $item[$sortname];
    }
    if (($sortname == 'count')) {
      arsort($result);
    } else {
      asort($result);
    }
    return $result;
  }
  
  //Itemplate
  public function request($id) {
    global $Urlmap;
    $this->id = $id;
    if (!isset($this->items[$id])) return 404;
    $url = $this->items[$this->id]['url'];
    if($Urlmap->pagenumber != 1) $url = rtrim($url, '/') . "/page/$Urlmap->pagenumber/";
    if ($Urlmap->url != $url) $Urlmap->Redir301($url);
  }
  
  public function AfterTemplated(&$s) {
    $redir = "<?php
    global \$Urlmap;
  \$url = '{$this->items[$this->id]['url']}';
    if(\$Urlmap->pagenumber != 1) \$url = rtrim(\$url, '/') . \"/page/\$Urlmap->pagenumber/\";
    if (\$Urlmap->url != \$url) \$Urlmap->Redir301(\$url);
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
    global $options, $Urlmap;
    $result = '';
    if ($this->id == 0) {
      $result .= "<ul>\n";
      $Sorted = $this->getsorted($this->sortname);
      foreach($Sorted as $id => $value ) {
        $result .= '<li><a href="'. $options->url. $this->items[$id]['url'] . '">'. $this->items[$id]['name'] . "</a>";
        if ($this->showcount) $result .= ' ('. $this->items[$id]['count'] . ')';
        $result .= "</li>\n";
      }
      
      $result .= "</ul>\n";
      return $result;
    }
    
    $items= $this->items[$this->id]['items'];
    $Posts = getnamedinstance('posts', 'tposts');
    $items = $Posts->SortAsArchive($items);
    $TemplatePost = &TTemplatePost::Instance();
    if ($this->lite) {
      $postsperpage = 1000;
      $list = array_slice($items, ($Urlmap->pagenumber - 1) * $postsperpage, $postsperpage);
      $result .= $TemplatePost->LitePrintPosts($list);
      $result .=$TemplatePost->PrintNaviPages($this->items[$this->id]['url'], $Urlmap->pagenumber, ceil(count($items)/ $postsperpage));
      return $result;
    } else{
      $list = array_slice($items, ($Urlmap->pagenumber - 1) * $options->postsperpage, $options->postsperpage);
      $TemplatePost = &TTemplatePost::Instance();
      $result .= $TemplatePost->PrintPosts($list);
      $result .=$TemplatePost->PrintNaviPages($this->items[$this->id]['url'], $Urlmap->pagenumber, ceil(count($items)/ $options->postsperpage));
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