<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tstaticpages extends titems implements itemplate {
  private $id;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'staticpages';
  }
  
  public function request($arg) {
    $this->id = (int)$arg;
  }
  
  public function getval($name) {
    return $this->items[$this->id][$name];
  }
  
  public function gettitle() {
    return $this->getval('title');
  }
  
public function gethead() { }
  public function getkeywords() {
    return $this->getval('keywords');
  }
  
  public function getdescription() {
    return $this->getval('description');
  }
  
  public function getcont() {
$theme = ttheme::instance();
return sprintf($theme->content->simple, $this->getval('filtered'));
  }
  
  public function add($title, $description, $keywords, $content) {
    $filter = tcontentfilter::instance();
    $title = tcontentfilter::escape($title);
    $linkgen = tlinkgenerator::instance();
    $url = $linkgen->createurl($title, 'menu', true);
    $urlmap = turlmap::instance();
    $this->items[++$this->autoid] = array(
    'idurl' => $urlmap->add($url, get_class($this),  $this->autoid),
    'url' => $url,
    'title' => $title,
    'filtered' => $filter->filter($content),
    'rawcontent' => $content,
    'description' => tcontentfilter::escape($description),
    'keywords' => tcontentfilter::escape($keywords)
    );
    $this->save();
    return $this->autoid;
  }
  
  public function edit($id, $title, $description, $keywords, $content) {
    if (!$this->itemexists($id)) return false;
    $filter = tcontentfilter::instance();
    $item = $this->items[$id];
    $this->items[$id] = array(
    'idurl' => $item['idurl'],
    'url' => $item['url'],
    'title' => $title,
    'filtered' => $filter->filter($content),
    'rawcontent' => $content,
    'description' => tcontentfilter::escape($description),
    'keywords' => tcontentfilter::escape($keywords)
    );
    $this->save();
    litepublisher::$urlmap->clearcache();
  }
  
  public function delete($id) {
    $urlmap = turlmap::instance();
    $urlmap->deleteitem($this->items[$id]['idurl']);
    parent::delete($id);
  }
  
}//class
?>