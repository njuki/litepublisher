<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class thomepage extends tmenu  {
  
  public static function i($id = 0) {
    return $id == 0 ? self::singleinstance(__class__) : self::iteminstance(__class__, $id);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'homepage' ;
    $this->data['image'] = '';
    $this->data['hideposts'] = false;
    $this->data['invertorder'] = false;
    $this->data['includecats'] = array();
    $this->data['excludecats'] = array();;
    $this->coinstances[] = new tcoevents($this, 'onbeforegetitems', 'ongetitems');
  }
  
  public function gettitle() {
  }
  
  public function getcont() {
    $result = '';
    $theme = ttheme::i();
    if (litepublisher::$urlmap->page == 1) {
      $image = $this->image;
      if ($image != '') {
        if (!strbegin($image, 'http://')) $image = litepublisher::$site->files . $image;
        $image = sprintf('<img src="%s" algt="Home image" />', $image);
      }
      $result .= $theme->simple($image . $this->content);
    }
    if ($this->hideposts) return $result;
    
    $items =  $this->getitems();
    $result .= $theme->getposts($items, false);
    return $result;
  }
  
  public function getitems() {
    if($result = $this->onbeforegetitems()) return $result;
    $posts = tposts::i();
    $perpage = litepublisher::$options->perpage;
    $from = (litepublisher::$urlmap->page - 1) * $perpage;
    $include = $this->data['includecats'];
    $exclude = $this->data['excludecats'];
    if ((count($include) == 0) && (count($exclude) == 0)) {
      $result = $posts->getpage(0, litepublisher::$urlmap->page, $perpage, $this->invertorder);
    } else {
      if (dbversion) {
        $poststable = $posts->thistable;
        $order = $this->invertorder ? 'asc' : 'desc';
        $catstable  = litepublisher::$db->prefix . 'categoriesitems';
        $where = '';
        if (count($include) > 0) $where .= sprintf('%s.item  in (%s)', $catstable , implode(',', $include));
        if (count($exclude) > 0) {
          if (count($include) > 0) $where .= ' and ';
          $where .= sprintf('%s.item  not in (%s)', $catstable , implode(',', $exclude));
        }
        
        $result = $posts->select("$poststable.status = 'published' and $poststable.id in
        (select DISTINCT post from $catstable  where $where)",
        "order by $poststable.posted $order limit $from, $perpage");
      } else {
        $catsposts = tcategories::i()->itemsposts;
        if (count($include) == 0) {
          $result = array_keys($posts->archives);
        } else {
          $result = array();
          foreach ($include as $id) {
            $result = array_merge($result, array_diff($catsposts->getposts($id), $result));
          }
        }
        
        foreach ($exclude as $id) {
          $result = array_diff($result, array_intersect($catsposts->getposts($id), $result));
        }
        
        $result = $posts->stripdrafts($result);
        $result = $posts->sortbyposted($result);
        if ($this->invertorder)       $result = array_reverse($result);
        $result = array_slice($result, (litepublisher::$urlmap->page - 1) * $perpage, $perpage);
      }
    }
    
    $this->callevent('ongetitems', array(&$result));
    return $result;
  }
  
}//class