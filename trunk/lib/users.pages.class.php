<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tuserpage extends tevents_itemplate {
[public $id;
public $item;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = dbversion;
    parent::create();
    $this->basename = 'userpage';
    $this->table = 'users';
$this->data['lite'] = false;
  }

public function geturl() {
return litepublisher::$urlmap->getidurl($this->item['idurl']);
}

public function request($id) {
$id = (int($id);
    $this->id = $id;
$users = tusers::instance();
    if (!$users->itemexists($id)) return 404;    
$this->item =$users->getitem($id);
    if ($this->lite && (litepublisher::$urlmap->page > 1)) {
      return sprintf("<?php turlmap::redir301('%s');",$this->geturl());
    }
}    

public function gettitle() {
return $this->item['name'];
}
  
  public function getcont() {
    $result = '';
    $theme = ttheme::instance();
    $perpage = $this->lite ? 1000 : litepublisher::$options->perpage;
    $posts = litepublisher::$classes->posts;
      $from = (litepublisher::$urlmap->page - 1) * $perpage;
if (dbversion) {
      $poststable = $posts->thistable;
      $items = $posts->select("$poststable.status = 'published' and $poststable.author = $this->id
      "order by $poststable.posted desc limit $from, $perpage");
      
      $result .= $theme->getposts($items, $this->lite);
    } else {
$items = array();
foreach ($posts->items as $id => $item) {
if (isset($item['status']) || !isset($item['author'])) continue;
if ($this->id == $item['author']) $items[] = $id;
}      

      $items = $posts->sortbyposted($items);
      $list = array_slice($items, (litepublisher::$urlmap->page - 1) * $perpage, $perpage);
      $result .= $theme->getposts($list, $this->lite);
    }
    
    $result .=$theme->getpages($this->geturl(), litepublisher::$urlmap->page, ceil($item['itemscount'] / $perpage));
    return $result;
}

public function add($id) {

}

public function delete($id) {
}

}//class