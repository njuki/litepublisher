<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tjsfiles extends titems {

  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = false;
    parent::create();
    $this->basename = 'jsfiles';
$this->data['filename'] = '/files/jsfile.js';
$this->data['revision'] = 1;
  }

public function save() {
$this->data['revision']++;
parent::save();
$this->assemble();
}
  
  public function add($filename) {
if (strbegin($filename,litepublisher::$paths->home)) $filename = substr($filename, strlen(litepublisher::$paths->home));
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

public function assemble() {
$home = litepublisher::$paths->home;
$s = '';
foreach ($this->items as $filename) {
$file = file_get_contents($home . $filename);
if ($file === false) $this->error(sprintf('Error read %s file', $filename));
$s .= $file;
}
$jsfile = $home . sprintf('%s.%s.js', $this->filename, $this->revision);
file_put_contents($jsfile, $s);
@chmod($$jsfile, 0666);
}

}//class

 
class tadminjsfiles extends tjsfiles {
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
$this->basename = 'adminjsfiles';
$this->data['filename'] = '/files/admin.jsfiles.js';
}

}//class