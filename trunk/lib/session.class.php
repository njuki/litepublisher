<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tsession {
public $prefix;

public function __construct () {
 //ini_set('session.name',COOKPREFIX.'sid');
$this->prefix = 'ses-' . str_replace((array('_', '.'), '-', litepublisher::$domain) . '-';
$truefunc = array($this, 'truefunc');
session_set_save_handler($truefunc,$truefunc, array($this,'read'), array($this,'write'), array($this,'destroy'), $truefunc);
}

public function truefunc() {
return true;
}

public function read($sessID) {
return tfilestorage::$memcache->get($this->prefix . $sessID);
}

public function write($sessID,$sessData) {
return tfilestorage::$memcache->set($this->prefix . $sessID,$sessData);
}

public function destroy($sessID) {
return tfilestorage::$memcache->delete($this->prefix . $sessID);
}

public static function init($usecookie = false) {
    ini_set('session.use_cookies', $usecookie);
    ini_set('session.use_only_cookies', $usecookie);
    ini_set('session.use_trans_sid', 0);
    session_cache_limiter(false);

    if (tfilestorage::$memcache) {
$ses = new __class__();
    } else {
ini_set('session.gc_probability', 1);
}
}

public static function start($id) {
self::init(false);
    session_id ($id);
    session_start();
  }
 
}//class