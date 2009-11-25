<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class tarchives extends titems implements  itemplate {
  public $date;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename   = 'archives';
    $this->table = 'posts';
    $this->data['lite'] = false;
    $this->data['showcount'] = false;
  }
  
  public function getwidgetcontent($id) {
    global $options;
    $result = '';
    
    foreach ($this->items as $date => $item) {
  $result  .= "<li><a rel=\"archives\" href=\"$options->url{$item['url']}\">{$item['title']}</a>";
    if ($this->showcount) $result .= "({$item['count']})";
      $result .= "</li>\n";
    }
    
    return $result;
  }
  
  public function GetHeadLinks() {
    global $options;
    $result = '';
    foreach ($this->items as $date => $item) {
  $result  .= "<link rel=\"archives\" title=\"{$item['title']}\" href=\"$options->url{$item['url']}\" />\n";
    }
    return $result;
  }
  
  protected function setlite($value) {
    if ($value != $this->lite) {
      $this->data['lite'] = $value;
      $this->Save();
    }
  }
  
  public function postschanged() {
    $posts = tposts::instance();
    $this->lock();
    $this->items = array();
    //sort archive by months
    $linkgen = tlinkgenerator::instance();
    if (dbversion) {
global $db;
    $res = $db->query("SELECT YEAR(posted) AS 'year', MONTH(posted) AS 'month', count(id) as 'count' FROM  $db->posts
      where status = 'published' GROUP BY YEAR(posted), MONTH(posted) ORDER BY posted DESC ");
      while ($r = $res->fetch(PDO::FETCH_ASSOC)) {
        $this->date = mktime(0,0,0, $r['month'] , 1, $r['year']);
        $this->items[$this->date] = array(
'idurl' => 0,
        'url' => $linkgen->Create($this, 'archive', false),
        'title' => tlocal::date($this->date, 'F Y'),
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
'idurl' => 0,
          'url' => $linkgen->Create($this, 'archive', false),
          'title' => tlocal::date($this->date, '%B %Y'),
          'year' => $d['year'],
          'month' =>$d['mon'],
'count' => 0,
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
    global $options;
    $urlmap = turlmap::instance();
    $urlmap->lock();
    $this->lock();
    //Compare links
    $old = $urlmap->GetClassUrls(get_class($this));
    foreach ($this->items as $date => $item) {
      $j = array_search($item['url'], $old);
      if (is_int($j))  {
        array_splice($old, $j, 1);
      } else {
        $this->items[$date]['idurl'] = $urlmap->Add($item['url'], get_class($this), $date);
      }
    }
    foreach ($old as $url) {
      $urlmap->delete($url);
    }
    
    $this->unlock();
    $urlmap->unlock();
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
    global $options, $urlmap;
    if (dbversion) {
      $item = $this->items[$this->date];
  $items = $this->db->idselect("status = 'published' and year(posted) = '{$item['year']}' and month(posted) = '{$item['month']} ORDER BY posted DESC ");
    } else {
      if (!isset($this->items[$this->date]['posts'])) return '';
      $items = &$this->items[$this->date]['posts'];
    }

$theme = ttheme::instance();
      $postsperpage = $this->lite ? 1000 : $options->postsperpage;
      $list = array_slice($items, ($urlmap->page - 1) * $postsperpage, $postsperpage);
      $result = $theme->getposts($list, $this->lite);
      $result .=$theme->getpages($this->items[$this->date]['url'], $urlmap->page, ceil(count($items)/ $postsperpage));
      return $result;
  }
  
}

?>