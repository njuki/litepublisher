<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tpullitems extends tdata {
protected $perpull;
protected $pull;
protected $modified;
protected $ongetitem;

  public static function i() {
    return getinstance(__class__);
  }
  
  public function __construct($basename, $perpull, $ongetitem) {
    parent::__construct();
$this->basename = $basename;
$this->perpull = $perpull;
$this->ongetitem = $ongetitem;
$this->pull = array();
$this->modified = array();
}

public function getitem($id) {
return call_user_func_array($this->ongetitem, array($id));
//$this->error('Call abastract method getitem in class' . get_class($this));
}

public function getfilename($idpull) {
return litepublisher::$paths->cache . $this->basename . '.pull.' . $idpull;
}

public function loadpull($idpull) {
      if (tfilestorage::loadvar($this->getfilename($idpull, $v)) {
$this->pull[$idpull] = $v;
} else {
$this->pull[$idpull] = array();
}
}

public function savepull($idpull) {
    if (!isset($this->modified[$idpull])) {
      litepublisher::$urlmap->onclose = array($this, 'savemodified', $idpull);
      $this->modified[$idpull] = true;
}
    }

public function savemodified($idpull) {
      return tfilestorage::savevar($this->getfilename($idpull), $this->pull[$idpull]);
}

public function getidpull($id) {
$idpull = int) floor ($id /$this->perpull);
if (!isset($this->pull[$idpull])) $this->loadpull($idpull);
return $idpull;
}

public function get($id) {
$idpull = $this->getidpull($id);
if (isset($this->pull[$idpull][$id])) return $this->pull[$idpull][$id];
$result = $this->getitem($id);
$this->pull[$idpull][$id] = $result;
$this->savepull($idpull);
return $result;
}

public function set($id, $item) {
$idpull = $this->getidpull($id);
$this->pull[$idpull][$id] = $item;
$this->savepull($idpull);
}

}//class