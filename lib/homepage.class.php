<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class thomepage extends tsinglemenu  {
  
  public static function i($id = 0) {
    return self::iteminstance(__class__, $id);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'homepage' ;
    $this->data['image'] = '';
    $this->data['hideposts'] = false;
    $this->data['invertorder'] = false;
    $this->data['includecats'] = array();
    $this->data['excludecats'] = array();;
    $this->data['archcount'] = 0;
    $this->data['parsetags'] = false;
    $this->coinstances[] = new tcoevents($this, 'onbeforegetitems', 'ongetitems');
  }
  
  public function gettitle() {
  }
  
  public function getcont() {
    $result = '';
    $theme = ttheme::i();
    if (litepublisher::$urlmap->page == 1) {
      if ($image = $this->image) {
        if (!strbegin($image, 'http://')) $image = litepublisher::$site->files . $image;
        $image = sprintf('<img src="%s" alt="Home image" />', $image);
      }
      $result .= $theme->simple($image . $this->content);
      if (litepublisher::$options->parsepost || $this->parsetags) {
        $result = $theme->parse($result);
      }
      
    }
    if ($this->hideposts) return $result;
    
    $items =  $this->getidposts();
    $result .= $theme->getpostsnavi($items, false, $this->url, $this->data['archcount']);
    return $result;
  }
  
  public function getidposts() {
    if($result = $this->onbeforegetitems()) return $result;
    $posts = tposts::i();
    $perpage = litepublisher::$options->perpage;
    $from = (litepublisher::$urlmap->page - 1) * $perpage;
    $include = $this->data['includecats'];
    $exclude = $this->data['excludecats'];
    if ((count($include) == 0) && (count($exclude) == 0)) {
      $this->data['archcount'] = $posts->archivescount;
      $result = $posts->getpage(0, litepublisher::$urlmap->page, $perpage, $this->invertorder);
    } else {
      $order = $this->invertorder ? 'asc' : 'desc';
      $result = $posts->select($this->getwhere(),
      'order by ' . $posts->thistable . ".posted $order limit $from, $perpage");
    }
    $this->callevent('ongetitems', array(&$result));
    return $result;
  }
  
  public function getwhere() {
    $result = '';
    $poststable = litepublisher::$db->prefix . 'posts';
    $catstable  = litepublisher::$db->prefix . 'categoriesitems';
    $include = $this->data['includecats'];
    $exclude = $this->data['excludecats'];
    
    if (count($include) > 0) {
      $result .= sprintf('%s.item  in (%s)', $catstable , implode(',', $include));
    }
    
    if (count($exclude) > 0) {
      if (count($include) > 0) $result .= ' and ';
      $result .= sprintf('%s.item  not in (%s)', $catstable , implode(',', $exclude));
    }
    
    
    if ($result == '') {
      return "$poststable.status = 'published'";
    } else {
      return "$poststable.status = 'published' and $poststable.id in
      (select DISTINCT post from $catstable  where $result)";
    }
  }
  
  public function postschanged() {
    if ($this->hideposts) return;
    $this->data['archcount'] = tposts::i()->db->getcount($this->getwhere());
    $this->save();
  }
  
}//class