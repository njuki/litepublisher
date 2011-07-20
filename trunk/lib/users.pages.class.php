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
$this->data['createpage'] = false;
  }

  public function select($where, $limit) {
    if (!$this->dbversion) $this->error('Select method must be called ffrom database version');
    if ($where != '') $where .= ' and ';
    $db = litepublisher::$db;
    $table = $this->thistable;
    $res = $db->query(
"select $table.*, $db->urlmap.url as url from $table
    left join  $db->urlmap on $db->urlmap.id  = $table.idurl
    where $where $limit");
    return $this->res2items($res);
  }
  
public function request($id) {
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
    $result = '';
    $theme = ttheme::instance();
    $perpage = $this->lite ? 1000 : litepublisher::$options->perpage;
    $posts = litepublisher::$classes->posts;
      $from = (litepublisher::$urlmap->page - 1) * $perpage;
if (dbversion) {
      $poststable = $posts->thistable;
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
      $list = array_slice($items, (litepublisher::$urlmap->page - 1) * $perpage, $perpage);
      $result .= $theme->getposts($list, $this->lite);
    }
    
    $result .=$theme->getpages($this->geturl(), litepublisher::$urlmap->page, ceil($item['itemscount'] / $perpage));
    return $result;
}

public function add($id, $name, $email, $website) {
$item = array(
'id' => $id,
'idurl' => 0,
'idview' => 0,
    'expired' => sqldate(),
    'registered' => sqldate(),
    'name' => $name,
    'email' => $email,
    'website' => $website,
    'ip' => '',
    'avatar' => 0
);

if ($this->createpage) {
    $linkgen = tlinkgenerator::instance();
    $url = $linkgen->addurl(new tarray2prop ($item), 'user');
$item['idurl'] = llitepublisher::$urlmap->add($url, get_class($this), $id);
}

if (dbversion) $this->db->insert_a($item);
$this->items[$id] = $item;
$this->save();
}

public function delete($id) {
$idurl = $this->getvalue($id, 'idurl');
if ($idurl > 0) litepublisher::$urlmap->deleteitem($idurl);
return $parent::delete($id);
}

  public function edit($id, array $values) {
$item = $this->getitem($id);
    foreach ($item as $k => $v) {
      if (isset($values[$k])) $item[$k] = $values[$k];
}
$item['id'] = $id;

if ($this->dbversion) $this->updateassoc($item);
$this->items[$id] = $item;
if (!$this->dbversion) $this->save();
}

}//class