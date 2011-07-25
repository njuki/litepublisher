<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tjsmerger extends titems {
public $texts;

  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = false;
    parent::create();
    $this->basename = 'jsmerger';
$this->data['revision'] = 1;
$this->addmap('texts', array());
  }

public function save() {
$this->data['revision']++;
parent::save();
$this->assemble();
}
  
  public function add($filename) {
if (strbegin($filename,litepublisher::$paths->home)) $filename = substr($filename, strlen(litepublisher::$paths->home));
if (empty($filename)) returnfalse;
if (in_array($filename, $this->items)) return false;
$this->items[] = $filename;
$this->save();
return count($this->items) - 1;
}

public function delete($filename) {
if (strbegin($filename,litepublisher::$paths->home)) $filename = substr($filename, strlen(litepublisher::$paths->home));
if (false === ($i = array_search($filename, $this->items))) return false;
array_delete($this->items, $i);
$this->save();
}

public function setfromstring($s) {{
$this->lock();
$this->items = array();
$a = explode("\n", trim($s));
foreach ($a as $filename) {
$this->add($filename);
}
$this->unlock();
}

  public function addtext($key, $s) {
$s = trim($s);
if (empty($s)) return;
if (in_array($s, $this->texts)) return false;
$this->texts[$key] = $s;
$this->save();
return count($this->texts) - 1;
}

public function deletetext($key) {
if (!isset($this->texts[$key])) return;
unset($this->texts[$key]);
$this->save();
return true;
}

public function getfilename() {
return sprintf('/files/js/%s.%s.js', $this->basename, $this->revision);
}

public function assemble() {
$home = litepublisher::$paths->home;
$s = '';
$theme = ttheme::instance();
foreach ($this->items as $filename) {
$filename = $theme->parse($filename);
$filename = str_replace('/', DIRECTORY_SEPARATOR, $filename);
if (false === ($file = file_get_contents($home . $filename))) $this->error(sprintf('Error read %s file', $filename));
$s .= $file;
}
$s .= implode("\n", $this->texts);
$jsfile =  $this->getfilename();
$realfile= $home . str_replace('/',DIRECTORY_SEPARATOR, $jsfile);
file_put_contents($realfile, $s);
@chmod($realfile, 0666);
$template = ttemplate::instance();
$template->data[$this->basename] = $jsfile;
$template->save();
litepublisher::$urlmap->clearcache();
$old = $home . str_replace('/',DIRECTORY_SEPARATOR, sprintf('/files/js/%s.%s.js', $this->basename, $this->revision - 1));
if (file_exists($old)) @unlink($old);
}

}//class

class tadminjsmerger extends tjsmerger {
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
$this->basename = 'jsadmin';
}

}//class

class tjscomments extends tjsmerger {
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
$this->basename = 'jscomments';
}

}//class