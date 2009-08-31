<?php

class TCommonTags extends TItems {
  //public $sortname;
  //public $showcount;
  public $title;
  public $PermalinkIndex;
  
  public $postsclass;
  public $PostPropname;
  protected $WidgetClass;
  protected $id;
  
  private $NewName;
  
  protected function CreateData() {
    parent::CreateData();
    //$this->AddEvents();
    $this->postsclass = 'TPosts';
    $this->Data['lite'] = false;
    $this->Data['sortname'] = 'count';
    $this->Data['showcount'] = true;
    $this->Data['maxcount'] =0;
    
    $this->PermalinkIndex = 'category';
    $this->PostPropname = 'categories';
    $this->WidgetClass = 'categories';
  }
  
  public function Save() {
    parent::Save();
    if (!$this->Locked())  {
      TTemplate::WidgetExpired($this);
    }
  }
  
  public function GetWidgetContent($id) {
    global $Options;
    $Template = TTemplate::Instance();
    $result = $Template->GetBeforeWidget($this->WidgetClass );
    
    $Sorted = &$this->GetSorted($this->sortname);
    if (($this->maxcount > 0) && ($this->maxcount < count($Sorted))) {
      $Sorted = array_slice($Sorted, 0, $this->maxcount, true);
    }
    
    foreach($Sorted as $id => $value ) {
  $result .= "<li><a href=\"$Options->url{$this->items[$id]['url']}\">{$this->items[$id]['name']}</a>";
      if ($this->showcount) $result .= ' ('. $this->items[$id]['count'] . ')';
      $result .= "</li>\n";
    }
    
    $result .= $Template->GetAfterWidget();
    return $result;
  }
  
  public function PostEdit($postid) {
    $posts = &GetInstance($this->postsclass);
    $post = &$posts->GetItem($postid);
    
  $list = $post->{$this->PostPropname};
    $this->Lock();
    foreach ($this->items as $id => $Item) {
      $toadd = in_array($id, $list);
      $i = array_search($postid, $Item['items']);
      if (is_int($i) && !$toadd) {
        array_splice($this->items[$id]['items'], $i, 1);
      }
      if ($toadd && !is_int($i)) {
        $this->items[$id]['items'][] = $postid;
      }
      
      $publ = $this->items[$id]['items'];
      $posts->StripDrafts($publ);
      $this->items[$id]['count'] = count($publ);
    }
    $this->Unlock();
  }
  
  public function PostDeleted($postid) {
    $this->Lock();
    foreach ($this->items as $id => $item) {
      $i = array_search($postid, $this->items[$id]['items']);
      if (is_int($i)) {
        array_splice($this->items[$id]['items'], $i, 1);
        $this->items[$id]['count'] = count($this->items[$id]['items']);
      }
    }
    $this->Unlock();
  }
  
  //for link generator
  public function name() {
    return $this->NewName;
  }
  
  public function Add($name, $slug = '') {
    if (empty($name)) return false;
    $id  = $this->IndexOf('name', $name);
    if ($id > 0) return $id;
    $this->Lock();
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
    $this->Unlock();
    $Urlmap =&TUrlmap::Instance();
    $dir = "/$this->PermalinkIndex/";
    if (substr($url, 0, strlen($dir)) == $dir) {
      $subdir = substr($url, strlen($dir));
      $subdir = trim($subdir, '/');
      if (strpos($subdir, '/')) {
        $Urlmap->Add($url, get_class($this),  $this->lastid);
      } else {
        $Urlmap->AddSubNode($this->PermalinkIndex, $subdir, get_class($this), $this->lastid);
      }
    } else {
      $Urlmap->Add($url, get_class($this),  $this->lastid);
    }
    $this->Added($this->lastid);
    $Urlmap->ClearCache();
    return $this->lastid;
  }
  
  public function Edit($id, $name, $url) {
    $item = $this->items[$id];
    if (($item['name'] != $name) || ($item['url'] != $url)) {
      $Urlmap = &TUrlmap::Instance();
      $Urlmap->Lock();
      
      $this->Lock();
      $item['name'] = $name;
      if ($item['url'] != $url) {
        $Urlmap->Delete($item['url']);
        $url = trim($url, '/');
        $this->NewName = $url == '' ? $name : $url;
        $Linkgen = &TLinkGenerator::Instance();
        $url = $Linkgen->Create($this, $this->PermalinkIndex );
        $Urlmap->Add($url, get_class($this),  array('id' => $id, 'page' => 1) );
        if ($item['url'] != $url) {
          $Urlmap->AddRedir($item['url'], $url);
        }
        
        $item['url'] = $url;
      }
      
      $this->items[$id] = $item;
      $this->Save();
      $Urlmap->ClearCache();
      $Urlmap->Unlock();
    }
  }
  
  public function Delete($id) {
    if (isset($this->items[$id])) {
      $posts = &GetInstance($this->postsclass);
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
      $urlmap = &TUrlmap::Instance();
      $urlmap->ClearCache();
      $this->Deleted($id);
    }
  }
  
  public function CreateNames($list) {
    if (is_string($list)) $list = explode(',', trim($list));
    $Result = array();
    $this->Lock();
    foreach ($list as $name) {
      $name = trim($name);
      if ($name == '') continue;
      $Result[] = $this->Add($name);
    }
    $this->Unlock();
    return $Result;
  }
  
  public function GetNames($list) {
    $Result =array();
    foreach ($list as $id) {
      if (!isset($this->items[$id])) continue;
      $Result[] = $this->items[$id]['name'];
    }
    return $Result;
  }
  
  public function GetLink($id) {
    global $Options;
    if ($this->ItemExists($id)) {
      return '<a href="'. $Options->url . $this->items[$id]['url'] . '">' . $this->items[$id]['name'] . '</a>';
    } else {
      return '';
    }
  }
  
  public function &GetSorted($sortname) {
    $Result = array();
    foreach($this->items as $id => $item) {
      $Result[$id] = $item[$sortname];
    }
    if (($sortname == 'count')) {
      arsort($Result);
    } else {
      asort($Result);
    }
    return $Result;
  }
  
  //template
  public function Request($id) {
    global $Urlmap;
    $this->id = $id;
    if ($id == 0) {
      $this->title = TLocal::$data['default']['categories'];
    } else {
      if (!isset($this->items[$id])) return 404;
      $url = $this->items[$this->id]['url'];
      if($Urlmap->pagenumber != 1) $url = rtrim($url, '/') . "/page/$Urlmap->pagenumber/";
      if ($Urlmap->url != $url) $Urlmap->Redir301($url);
      
      $this->title = $this->items[$id]['name'];
    }
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
  
  public function GetTemplateContent() {
    global $Options, $Urlmap;
    $result = '';
    if ($this->id == 0) {
      $result .= "<ul>\n";
      $Sorted = &$this->GetSorted($this->sortname);
      foreach($Sorted as $id => $value ) {
        $result .= '<li><a href="'. $Options->url. $this->items[$id]['url'] . '">'. $this->items[$id]['name'] . "</a>";
        if ($this->showcount) $result .= ' ('. $this->items[$id]['count'] . ')';
        $result .= "</li>\n";
      }
      
      $result .= "</ul>\n";
      return $result;
    }
    
    $items= $this->items[$this->id]['items'];
    $Posts = &GetInstance($this->postsclass);
    $items = $Posts->SortAsArchive($items);
    $TemplatePost = &TTemplatePost::Instance();
    if ($this->lite) {
      $postsperpage = 1000;
      $list = array_slice($items, ($Urlmap->pagenumber - 1) * $postsperpage, $postsperpage);
      $result .= $TemplatePost->LitePrintPosts($list);
      $result .=$TemplatePost->PrintNaviPages($this->items[$this->id]['url'], $Urlmap->pagenumber, ceil(count($items)/ $postsperpage));
      return $result;
    } else{
      $list = array_slice($items, ($Urlmap->pagenumber - 1) * $Options->postsperpage, $Options->postsperpage);
      $TemplatePost = &TTemplatePost::Instance();
      $result .= $TemplatePost->PrintPosts($list);
      $result .=$TemplatePost->PrintNaviPages($this->items[$this->id]['url'], $Urlmap->pagenumber, ceil(count($items)/ $Options->postsperpage));
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
      $this->Save();
    }
  }
  
}//class

?>