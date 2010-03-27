<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tcodedoc extends tpost {
  public $doc;
  public $doctable;
  
  public static function instance($id = 0) {
    return parent::iteminstance('post', __class__, $id);
  }
  
  protected function create() {
    parent::create();
    $this->doctable = 'codedoc';
    $this->data['doc'] = &$this->doc;
    $this->doc = array(
    'id' => 0,
    'class'  => '',
    'parentclass' => '',
    'source' => '',
    'childs' => array(),
    'interfaces' => '',
    'dependent' => '',
    'methods' => '',
    'properties' => '',
    'classevents' => '',
    'example' => ''
    );
  }

  public function getschemalink() {
    return 'codedoc';
  }
  
  public function __get($name) {
if ($name == 'id') return $this->data['id'];
    if (array_key_exists($name, $this->doc)) return $this->doc[$name];
    return parent::__get($name);
  }
  
  public function __set($name, $value) {
if ($name == 'id') return $this->setid($value);
    if (array_key_exists($name, $this->doc)) {
      $this->doc[$name] = $value;
      return true;
    }
    return parent::__set($name, $value);
  }
  
  public function __isset($name) {
    return array_key_exists($name, $this->doc) || parent::__isset($name);
  }
  
  protected function LoadFromDB() {
    if (!parent::LoadFromDB())  return false;
    if ($a = $this->getdb($this->doctable)->getitem($this->id)) {
      $this->doc = $a;
      return true;
    }
    return false;
  }
  
  protected function SaveToDB() {
    parent::SaveToDB();
    $this->doc['id'] = $this->id;
    $this->getdb($this->doctable)->updateassoc($this->doc);
  }
  
  public function addtodb() {
    $id = parent::addtodb();
    $this->doc['id'] = $id;
    $this->getdb($this->doctable)->add($this->doc);
    return $this->id;
  }
  
  public function updatechilds() {
    if ($this->class = '') {
      $this->childs = '';
    } else {
      $db = $this->getdb($this->doctable);
      $childs = $db->idselect(sprintf('parentclass = %s order by class', dbquote($this->class)));
      $this->childs = implode(',', $childs);
      if (($this->parentclass != '')&& ($id = $db->findid('class = ' . dbquote($this->parentclass))) {
        $parentchilds = $db->idselect('parentclass = ' . dbquote($this->parentclass));
        $db->setvalue($id, 'childs', implode(',', $parentchilds));
      }
    }
  }
  
  //tags
  private function  getword($word) {
    if ($word == '') return '';
    $wiki = twikiwords::instance();
    return $wiki->getword($word);
  }
  
  private function getwords($words) {
    if ($words == '') return '';
    $links = array();
    foreach (explode(',', $words) as $word) {
      if ($link = $this->getword($word)) {
        $links[] = $link;
      }
    }
    return implode(', ', $links);
  }
  
  protected function getparentlink() {
    return $this->getword($this->parentclass);
  }
  
  protected function getchildlinks(){
    if ($this->childs == '') return '';
    $db = litepublisher::$db;
    $childs = $db->res2assoc($db->query("select id, title from db->posts, url from $db->urlmap
    where $db->posts.id in ($this->childs) and $db->urlmap.id = $db->posts.idurl"));
    
    $links = array();
    foreach ($childs as $child) {
      $links[] = sprintf('<a href="%1$s%2$s" title="%3$s">3%$s</a>', litepublisher::$options->url, $child['url'], $child['title']);
    }
    return implode(', ', $links);
  }
  
  protected function getinterfacelinks() {
    return $this->getwords($this->interfaces);
  }
  
  protected function getdependentlinks() {
    return $this->getwords($this->dependent);
  }
  
  protected function getsourcelink() {
    if ($source = $this->doc['source']) {
      return sprintf('<a href="%1$s/source/%2$s" title="%2$s">%2$s</a>', litepublisher::$options->url, $source);
    }
    return '';
  }
  
  protected function getcontentpage($page) {
    $result = '';
    if ($page == 1) $result .= $this->getdoccontent();
    $result .= parent::getcontentpage($page);
    
    if (($page == 1) && !empty($this->doc['example'])) {
      $example = str_replace(array('"', "'", '$'), array('&quot;', '&#39;', '&#36;'), htmlspecialchars($this->example));
      $result .= "sprintf('<code><pre>%s</pre></code>', $example);
    }
    return $result;
  }
  
  public function getdoccontent() {
    $this->checklang();
    $lang = tlocal::instance('doc');
    $args = targs::instance();
    foreach (array('type', 'state', 'prio') as $prop) {
      $value = $this->$prop;
      $args->$prop = $lang->$value;
    }
    $args->reproduced = $this->reproduced ? $lang->yesword : $lang->noword;
    if ($this->assignto <= 1) {
      $profile = tprofile::instance();
      $args->assignto = $profile->nick;
    } else {
      $users = tusers::instance();
      $account = $users->getaccount($this->assignto);
      $args->assignto = $account['name'];
    }
    
    ttheme::$vars['doc'] = $this;
    $theme = ttheme::instance();
    $tml = file_get_contents($this->resource . 'doc.tml');
    $result = $theme->parsearg($tml, $args);
    if ($this->poll > 1) {
      $polls = tpolls::instance();
      $result .= $polls->gethtml($this->poll);
    }
    return $result;
  }
  
  protected function getresource() {
    return dirname(__file__) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;
  }
  
  public function checklang() {
    if (!isset(tlocal::$data['doc'])) {
      tlocal::loadini($this->resource . litepublisher::$options->language . '.ini');
      tfiler::serialize(litepublisher::$paths->languages . litepublisher::$options->language . '.php', tlocal::$data);
      tfiler::ini2js(tlocal::$data , litepublisher::$paths->files . litepublisher::$options->language . '.js');
    }
  }
  
}//class
?>