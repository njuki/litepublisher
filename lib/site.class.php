<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tsite ext0ends tevents_storage {
private $users;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'site';
  }
  
  public function __set($name, $value) {
    if ($name == 'url') return $this->seturl($value);
    if (in_array($name, $this->eventnames)) {
      $this->addevent($name, $value['class'], $value['func']);
    } elseif (!array_key_exists($name, $this->data)  || ($this->data[$name] != $value)) {
      $this->data[$name] = $value;
      $this->save();
    }
    return true;
  }
  
  public function geturl() {
    if ($this->fixedurl) return $this->data['url'];
    return 'http://'. litepublisher::$domain;
  }
  
  public function getfiles() {
    if ($this->fixedurl) return $this->data['files'];
    return 'http://'. litepublisher::$domain;
  }
  
  public function seturl($url) {
    $url = rtrim($url, '/');
    $this->data['url'] = $url;
    $this->data['files'] = $url;
    $this->subdir = '';
    if ($i = strpos($url, '/', 10)) {
      $this->subdir = substr($url, $i);
    }
    $this->save();
  }
  
  public function getversion() {
    return litepublisher::$options->data['version'];
  }
  
  public function getlanguage() {
    return litepublisher::$options->data['language'];
  }
  

public function getuserlink() {
if ($id = litepublisher::$options->user) {
if (!isset($this->users)) $this->users = array();
if (isset($this->users[$id])) return $this->users;
$item = tusers::i()->getitem($id);
if ($item['website']) {
$result = sprintf('<a href="%s">%s</a>', $item['website'], $item['name']);
} else {
$db = litepublisher::$db;
    $item= $db->res2assoc($db->query("select $db->userpage.*, $db->urlmap.url as url from $db->userpage
    left join  $db->urlmap on $db->urlmap.id  = $db->userpage.idurl
    where id = $id limit 1"));

$result = sprintf('<a href="%s%s">%s</a>', $this->url, $item['url'], $item['name']);
}
$this->users[$id] = $result;
return $result;
}
return '';
}

}//class