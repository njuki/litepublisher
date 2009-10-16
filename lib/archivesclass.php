<?php

class TArchives extends TItems implements  ITemplate {
  public $date;
  
  public static function &Instance() {
    return GetNamedInstance('archives', __class__);
  }
  
  protected function CreateData() {
    parent::CreateData();
    $this->basename   = 'archives';
    $this->table = 'posts';
    $this->Data['lite'] = false;
    $this->Data['showcount'] = false;
  }
  
  public function GetWidgetContent($id) {
    global $Options;
    $result = '';
    
    foreach ($this->items as $date => $item) {
  $result  .= "<li><a rel=\"archives\" href=\"$Options->url{$item['url']}\">{$item['title']}</a>";
    if ($this->showcount) $result .= "({$item['count']})";
      $result .= "</li>\n";
    }
    
    return $result;
  }
  
  public function GetHeadLinks() {
    global $Options;
    $result = '';
    foreach ($this->items as $date => $item) {
  $result  .= "<link rel=\"archives\" title=\"{$item['title']}\" href=\"$Options->url{$item['url']}\" />\n";
    }
    return $result;
  }
  
  protected function Setlite($value) {
    if ($value != $this->lite) {
      $this->Data['lite'] = $value;
      $this->Save();
    }
  }
  
  public function PostsChanged() {
    $posts = &TPosts::Instance();
    $this->lock();
    $this->items = array();
    //sort archive by months
    $Linkgen = &TLinkGenerator::Instance();
    if ($this->dbversion) {
      $db = $this->db;
    $res = $db->query("SELECT YEAR(created) AS 'year', MONTH(created) AS 'month', count(id) as 'count' FROM  {$db->prefix}posts
      where status = 'published' GROUP BY YEAR(created), MONTH(created) ORDER BY created DESC ");
      while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
        $this->date = mktime(0,0,0, $r['month'] , 1, $r['year']);
        $this->items[$this->date] = array(
        'url' => $Linkgen->Create($this, 'archive', false),
        'title' => TLocal::date($this->date, 'F Y'),
        'year' => $r['year'],
        'month' => $r['month'],
        'count' => $r['count']
        );
      }
    } else {
      foreach ($posts->archives as $id => $date) {
        $d = getdate($date);
        $this->date = mktime(0,0,0, $d['mon'] , 1, $d['year']);
        if (!isset($this->items[$this->date])) {
          $this->items[$this->date] = array(
          'url' => $Linkgen->Create($this, 'archive', false),
          'title' => TLocal::date($this->date, 'F Y'),
          'year' => $d['year'],
          'month' =>$d['mon'],
          'posts' => array()
          );
        }
        $this->items[$this->date]['posts'][] = $id;
      }
      foreach ($this->items as $date => $item) $this->items[$date]['count'] = count($item['posts']);
    }
    $this->CreatePageLinks();
    $this->unlock();
  }
  
  public function CreatePageLinks() {
    global $Options;
    $Urlmap = &TUrlmap::Instance();
    $Urlmap->lock();
    $this->lock();
    //Compare links
    $old = $Urlmap->GetClassUrls(get_class($this));
    foreach ($this->items as $date => $item) {
      $j = array_search($item['url'], $old);
      if (is_int($j))  {
        array_splice($old, $j, 1);
      } else {
        $Urlmap->Add($item['url'], get_class($this), $date);
      }
    }
    foreach ($old as $url) {
      $Urlmap->Delete($url);
    }
    
    $this->unlock();
    $Urlmap->unlock();
  }
  
  //ITemplate
  public function request($date) {
    $date = (int) $date;
    if (!isset($this->items[$date])) return 404;
    $this->date = $date;
  }
  
  public function gettitle() {
    return $this->items[$this->date]['title'];
  }
  
public function gethead() {}
public function getkeywords() {}
public function getdescription() {}
  
  public function GetTemplateContent() {
    global $Options, $Urlmap;
    if ($this->dbversion) {
      $item = $this->items[$this->date];
  $items = $this->db->idselect("status = 'published' and year(created) = '{$item['year']}' and month(created) = '{$item['month']} ORDER BY created DESC ");
    } else {
      if (!isset($this->items[$this->date]['posts'])) return '';
      $items = &$this->items[$this->date]['posts'];
    }
    $TemplatePost = TTemplatePost::Instance();
    if ($this->lite) {
      $postsperpage = 1000;
      $list = array_slice($items, ($Urlmap->pagenumber - 1) * $postsperpage, $postsperpage);
      $result = $TemplatePost->LitePrintPosts($list);
      $result .=$TemplatePost->PrintNaviPages($this->items[$this->date]['url'], $Urlmap->pagenumber, ceil(count($items)/ $postsperpage));
      return $result;
    } else {
      $list = array_slice($items, ($Urlmap->pagenumber - 1) * $Options->postsperpage, $Options->postsperpage);
      $result = $TemplatePost->PrintPosts($list);
      $result .=$TemplatePost->PrintNaviPages($this->items[$this->date]['url'], $Urlmap->pagenumber, ceil(count($items)/ $Options->postsperpage));
      return $result;
    }
  }
  
}

?>