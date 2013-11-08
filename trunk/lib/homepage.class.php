<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class thomepage extends tsinglemenu  {
  public $cacheposts;
  
  public static function i($id = 0) {
    return self::iteminstance(__class__, $id);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'homepage' ;
    $this->data['image'] = '';
        $this->data['showmidle'] = false;
                $this->data['midlecat'] = 0;
    $this->data['showposts'] = true;
    $this->data['invertorder'] = false;
    $this->data['includecats'] = array();
    $this->data['excludecats'] = array();;
        $this->data['showpagenator'] = true;
    $this->data['archcount'] = 0;
    $this->data['parsetags'] = false;
    $this->coinstances[] = new tcoevents($this, 'onbeforegetitems', 'ongetitems');
    $this->cacheposts = false;
  }
  
  public function getindex_tml() {
  $theme = ttheme::i();
  if (!empty($theme->templates['index.home'])) return $theme->templates['index.home'];
  return false;
  }
  
  public function gethead() {
    $result = parent::gethead();
    if ($this->showposts) {
      $items =  $this->getidposts();
      $result .= tposts::i()->getanhead($items);
    }
    return ttheme::i()->parse($result);
  }
  
  public function gettitle() {
  }
  
  public function getimg() {
        if ($url = $this->image) {
        if (!strbegin($url, 'http://')) $url = litepublisher::$site->files . $url;
return sprintf('<img src="%s" alt="Home image" />', $url);
      }
      return '';
}

  public function getbefore() {
      if ($result = $this->getimg() . $this->content) {
          $theme = ttheme::i();
           $result = $theme->simple($result);
      if ($this->parsetags || litepublisher::$options->parsepost) $result = $theme->parse($result);
      return $result;
}
return '';
    }
  
  public function getcont() {
    $result = '';
    if (litepublisher::$urlmap->page == 1) {
    $result .= $this->getbefore();
        if ($this->showmidle && $this->midlecat) $result .= $this->getmidle();
   }
    
    if ($this->showposts) $result .= $this->getpostnavi();
        return $result;
  }
  
  public function getpostnavi() {
          $items =  $this->getidposts();
            $theme = ttheme::i();
        $result = $theme->getposts($items, false);
    if ($this->showpagenator) $result .= $theme->getpages($this->url, litepublisher::$urlmap->page, ceil($this->data['archcount'] / litepublisher::$options->perpage));
return $result;
}
  
  public function getidposts() {
    if ($this->cacheposts) return $this->cacheposts;
    if($result = $this->onbeforegetitems()) return $result;
    $posts = tposts::i();
    $perpage = litepublisher::$options->perpage;
    $from = (litepublisher::$urlmap->page - 1) * $perpage;
          $order = $this->invertorder ? 'asc' : 'desc';
          
              $p = litepublisher::$db->prefix . 'posts';
    $ci = litepublisher::$db->prefix . 'categoriesitems';

    if ($where = $this->getwhere()) {
            $result = $posts->db->res2id($posts->db->query("select $p.id as id, $ci.post as post from $p, $ci
    where    $where and $p.id = $ci.post and $p.status = 'published'
      order by  $p.posted $order limit $from, $perpage"));
      
    $result = array_unique($result);
        $posts->loaditems($result);
} else {        
      $this->data['archcount'] = $posts->archivescount;
      $result = $posts->getpage(0, litepublisher::$urlmap->page, $perpage, $this->invertorder);
    }
    
    $this->callevent('ongetitems', array(&$result));
    $this->cacheposts = $result;
    return $result;
  }
  
  public function getwhere() {
    $result = '';
    $p = litepublisher::$db->prefix . 'posts';
    $ci = litepublisher::$db->prefix . 'categoriesitems';
    $include = $this->data['includecats'];
    $exclude = $this->data['excludecats'];
        if ($this->showmidle && $this->midlecat) $exclude[] = $this->midlecat;
    
    if (count($include) > 0) {
      $result .= sprintf('%s.item  in (%s)', $ci, implode(',', $include));
    }
    
    if (count($exclude) > 0) {
      if (count($include) > 0) $result .= ' and ';
      $result .= sprintf('%s.item  not in (%s)', $ci, implode(',', $exclude));
    }

return $result;    
  }
  
  public function postschanged() {
    if (!$this->showposts || !$this->showpagenator) return;
    
    if ($where = $this->getwhere()) {
    $db = $this->db;
                      $p = litepublisher::$db->prefix . 'posts';
    $ci = litepublisher::$db->prefix . 'categoriesitems';

    $res = $db->query("select count(DISTINCT $p.id) as count from $p, $ci
    where    $where and $p.id = $ci.post and $p.status = 'published'");
    
if ($r = $res->fetch_assoc()) $this->data['archcount'] = (int) $r['count'];
      } else {        
      $this->data['archcount'] = tposts::i()->archivescount;
    }

    $this->save();
  }
  
    public function getmidletitle() {
    if ($idcat = $this->midlecat) {
    return $this->getdb('categories')->getvalue($idcat, 'title');
    }
    
    return '';
    }
  
  public function getmidle() {
    $result = '';
    $posts = tposts::i();
    $p = $posts->thistable;
    $ci = litepublisher::$db->prefix . 'categoriesitems';
            $items = $posts->db->res2id($posts->db->query("select $p.id as id, $ci.post as post from $p, $ci
    where    $ci.item = $this->midlecat and $p.id = $ci.post and $p.status = 'published'
      order by  $p.posted desc limit " . litepublisher::$options->perpage));
    
    if (!count($items)) return '';
    $posts->loaditems($items);
    ttheme::$vars['lang'] = tlocal::i('default');    
        $theme = ttheme::i();
    $tml = $theme->templates['content.home.midle.post'];
    foreach($items as $id) {
      ttheme::$vars['post'] = tpost::i($id);
      $result .= $theme->parse($tml);
      // has $author.* tags in tml
      if (isset(ttheme::$vars['author'])) unset(ttheme::$vars['author']);
    }
    
$tml = $theme->templates['content.home.midle'];
    if ($tml) $result = str_replace('$post', $result, $this->parse($tml));
    unset(ttheme::$vars['post']);
return $result;
}
  
}//class