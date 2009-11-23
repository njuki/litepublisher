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

public function getvalue($name0) {
return $this->items[$this->id][$name];
}

  public function gettitle() {
return $this->getvalue('title');
}

  public function gethead() { }
  public function getkeywords() {
return $this->getvalue('keywords');
}

  public function getdescription() {
return $this->getvalue('description');
}

  public function GetTemplateContent() {
return $this->getvalue('filtered');
}

public function add($title, $content) {
$filter = tcontentfilter::instance();
$title = tcontentfilter::escape($title);
$linkgen = tlinkgenerator::instance();
$url = $linkgen->createurl($title, 'post', true);
$urlmap = turlmap::instance();
$this->items[++$this->autoid] = array(
'idurl' => $urlmap->add($url, get_class($this),  $this->autoid),
'url' => $url,
'title' => $title,
'filtered' => $filter->filter($content),
'rawcontent' => $content,
'description' => '',
'keywords' => ''
);
$this->save();
return $this->autoid;
}

public function edit($id, $title, $content, $description, $keywords) {
$item = $this->items[$id];
$this->items[$id] = array(
'idurl' => $item['idurl'],
'url' => $item['url'],
'title' => $title,
'filtered' => $filter->filter($content),
'rawcontent' => $content,
'description' => $description,
'keywords' => $keywords
);
$this->save();
$urlmap = turlmap::instance();
$urlmap->clearcache();
}

public function delete($id) {
$urlmap = turlmap::instance();
$urlmap->deleteitem($this->items[$id]['idurl']);
parent::delete($id);
}


}//class
?>