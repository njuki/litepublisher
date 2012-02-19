<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tprivatefiles extends titems {
public $id;
public $item;

  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = dbversion;
    parent::create();
    $this->basename = 'files.private';
    $this->addevents('changed', 'edited', 'ongetfilelist');
  }

public function __get($name) {
if (isset($this->item[$name])) return $this->item[$name];
return parent::__get($name);
}

public function error500() {
}
  
  public function request($id) {
$files = tfiles::i();
if (!$files->itemexists($id)) return 404;
$item = $files->getitem($id);
$filename = '/files/' . $item['filename'];
if ($item['idperm'] == 0) {
if ($filename == litepublisher::$urlmap->url) return $this->error500();
return turlmap::redir301($filename);
}

$this->id = $id;
$this->item = $item;

$perm = tperm::i($item['idperm']);
$result = $perm->getheader($this);



}

//class