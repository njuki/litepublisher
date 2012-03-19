<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tregservices extends titems {

    public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
$this->dbversion = false;
    parent::create();
$this->basename = 'regservices' .DIRECTORY_SEPARATOR . 'index';
}

public function add(tgoogleregservice $service) {
$id = $this->additem(array(
'class' => get_class($service),
));
$service->id = $id;
$service->save();
$this->update_widget();
return $id;
}

  public function request($arg) {
$this->cache = false;
$id = empty($_GET['id']) ? 0 : (int) $_GET['id'];
if (!$this->itemexists($id)) return 404;
$service = getinstance($this->items[$id]['class']);
if (!$service->valid) return 403;
$url = $service->getauthurl();
return turlmap::redir($url);
}

}//class