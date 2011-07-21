<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tuserpages extends titems implements itemplate {
  public $id;
  
  public static function instance() {
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
  
  public function request($id) {
    if ($id == 'website') {
      $id = isset($_GET['id']) ? (int) $_GET['id'] : 1;
      if (!$this->itemexists($id)) return 404;
      $item = $this->getitem($id);
      $website = $item['website'];
      if (!strpos($website, '.')) $website = litepublisher::$site->website . litepublisher::$site->home;
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
  
public function gethead() {}
  
  public function getcont() {
    $item =$this->getitem($this->id);
    $theme = ttheme::instance();
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
      foreach ($posts->items as $id => $item) {
        if (isset($item['status']) || !isset($item['author'])) continue;
        if ($this->id == $item['author']) $items[] = $id;
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
    $this->addurl($item);
    $this->items = $item;
    $this->save();
    unset($item['url']);
    $item['id'] = $id;
    if (dbversion) $this->db->updateassoc($item);
  }
  
  private function addurl(array &$item) {
    $item['url'] = '';
    $linkgen = tlinkgenerator::instance();
    $item['url'] = $linkgen->addurl(new tarray2prop ($item), 'user');
    $item['idurl'] = litepublisher::$urlmap->add($item['url'], get_class($this), $item['id']);
    return $item['idurl'];
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
    'description' => ''
    );
    
    if ($this->createpage) {
      $users = tusers::instance();
      if ('approved' == $users->getvalue($id, 'status')) $this->addurl($item);
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
    $item['content'] = tcontentfilter::instance()->filter($item['rawcontent']);
    if ($url && ($url != $item['url'])) {
      if ($item['idurl'] == 0) {
        $item['idurl'] = litepublisher::$urlmap->add($url, get_class($this), $id);
      } else {
        litepublisher::$urlmap->addredir($item['url'], $url);
        litepublisher::$urlmap->setidurl($item['idurl'], $url);
      }
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