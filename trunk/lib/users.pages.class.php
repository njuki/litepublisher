<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tuserpages extends titems implements itemplate {
  public $id;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = dbversion;
    parent::create();
    $this->basename = 'userpage';
    $this->table = 'userpage';
    $this->data['lite'] = false;
    $this->data['createpage'] = true;
  }
  
  public function __get($name) {
    if (in_array($name, array('name', 'url', 'website'))) {
      return $this->getvalue($this->id, $name);
    }
    
    return parent::__get($name);
  }

public function getmd5email() {
return md5($this->getvalue($this->id, 'email'));
}
 
    public function getgravatar) {
return sprintf('<img src="http://www.gravatar.com/avatar/%s?s=50&r=g&amp;d=wavatar" alt="avatar" />', $this->md5email());
}
 
  public function select($where, $limit) {
    if (!$this->dbversion) $this->error('Select method must be called ffrom database version');
    if ($where != '') $where = ' where ' . $where;
    $db = litepublisher::$db;
    $table = $this->thistable;
    $res = $db->query(
    "select $table.*, $db->urlmap.url as url from $table
    left join  $db->urlmap on $db->urlmap.id  = $table.idurl
    $where $limit");
    return $this->res2items($res);
  }
  
  public function getitem($id) {
    $item = parent::getitem($id);
    if (!isset($item['url'])) {
      $item['url'] = $item['idurl'] == 0 ? '' : litepublisher::$urlmap->getidurl($item['idurl']);
      $this->items[$id]['url'] = $item['url'];
    }
    return $item;
  }
  
  public function request($id) {
    if ($id == 'url') {
      $id = isset($_GET['id']) ? (int) $_GET['id'] : 1;
      if (!$this->itemexists($id)) return 404;
      $item = $this->getitem($id);
      $website = $item['website'];
      if (!strpos($website, '.')) $website = litepublisher::$site->url . litepublisher::$site->home;
      if (!strbegin($website, 'http://')) $website = 'http://' . $website;
      return "<?php turlmap::redir('$website');";
    }
    
    $this->id = (int) $id;
    if (!$this->itemexists($id)) return 404;
    $item =$this->getitem($id);
    if ($this->lite && (litepublisher::$urlmap->page > 1)) {
      return sprintf("<?php turlmap::redir301('%s');",$item['url']);
    }
  }
  
  public function gettitle() {
    return $this->getvalue($this->id, 'name');
  }
  
  public function getkeywords() {
    return $this->getvalue($this->id, 'keywords');
  }
  
  public function getdescription() {
    return $this->getvalue($this->id, 'description');
  }
  
  public function getidview() {
    return $this->getvalue($this->id, 'idview');
  }
  
  public function setidview($id) {
    $this->setvalue($this->id, 'idveiw');
  }
  
  public function gethead() {
    return $this->getvalue($this->id, 'head');
  }
  
  public function getcont() {
    $item =$this->getitem($this->id);
    $theme = tview::getview($this)->theme;
    $result = empty($item['content']) ? '' : $theme->simple($item['content']);
    $perpage = $this->lite ? 1000 : litepublisher::$options->perpage;
    $posts = litepublisher::$classes->posts;
    $from = (litepublisher::$urlmap->page - 1) * $perpage;
    if (dbversion) {
      $poststable = $posts->thistable;
      $count = $posts->db->getcount("$poststable.status = 'published' and $poststable.author = $this->id");
      $items = $posts->select("$poststable.status = 'published' and $poststable.author = $this->id",
      "order by $poststable.posted desc limit $from, $perpage");
      $result .= $theme->getposts($items, $this->lite);
    } else {
      $items = array();
      foreach ($posts->items as $id => $postitem) {
        if (isset($postitem['status']) || !isset($postitem['author'])) continue;
        if ($this->id == $postitem['author']) $items[] = $id;
      }
      
      $items = $posts->sortbyposted($items);
      $count = count($items);
      $list = array_slice($items, (litepublisher::$urlmap->page - 1) * $perpage, $perpage);
      $result .= $theme->getposts($list, $this->lite);
    }
    $result .=$theme->getpages($item['url'], litepublisher::$urlmap->page, ceil($count / $perpage));
    return $result;
  }
  
  public function addpage($id) {
    $item = $this->getitem($id);
    if ($item['idurl'] > 0) return $item['idurl'];
    $item = $this->addurl($item);
    $this->items = $item;
    if (dbversion) {
      unset($item['url']);
      $item['id'] = $id;
      $this->db->updateassoc($item);
    } else {
      $this->save();
    }
  }
  
  private function addurl(array $item) {
    $item['url'] = '';
    $linkgen = tlinkgenerator::i();
    $item['url'] = $linkgen->addurl(new tarray2prop ($item), 'user');
    $item['idurl'] = litepublisher::$urlmap->add($item['url'], get_class($this), $item['id']);
    return $item;
  }
  
  public function add($id, $name, $email, $website) {
    $item = array(
    'id' => $id,
    'idurl' => 0,
    'idview' => 1,
    'registered' => sqldate(),
    'name' => $name,
    'email' => $email,
    'website' => $website,
    'ip' => '',
    'avatar' => 0,
    'content' => '',
    'rawcontent' => '',
    'keywords' => '',
    'description' => '',
    'head' => ''
    );
    
    if ($this->createpage) {
      $users = tusers::i();
      if ('approved' == $users->getvalue($id, 'status'))  $item = $this->addurl($item);
    }
    $this->items[$id] = $item;
    unset($item['url']);
    if (dbversion) $this->db->insert_a($item);
    $this->save();
  }
  
  public function delete($id) {
    $idurl = $this->getvalue($id, 'idurl');
    if ($idurl > 0) litepublisher::$urlmap->deleteitem($idurl);
    return parent::delete($id);
  }
  
  public function edit($id, array $values) {
    $item = $this->getitem($id);
    $url = isset($values['url']) ? $values['url'] : '';
    unset($values['url'], $values['idurl'], $values['id']);
    foreach ($item as $k => $v) {
      if (isset($values[$k])) $item[$k] = $values[$k];
    }
    $item['id'] = $id;
    $item['content'] = tcontentfilter::i()->filter($item['rawcontent']);
    if ($url && ($url != $item['url'])) {
      if ($item['idurl'] == 0) {
        $item['idurl'] = litepublisher::$urlmap->add($url, get_class($this), $id);
      } else {
        litepublisher::$urlmap->addredir($item['url'], $url);
        litepublisher::$urlmap->setidurl($item['idurl'], $url);
      }
      $item['url'] = $url;
    }
    
    $this->items[$id] = $item;
    if ($this->dbversion) {
      unset($item['url']);
      $this->db->updateassoc($item);
    } else {
      $this->save();
    }
  }
  
}//class