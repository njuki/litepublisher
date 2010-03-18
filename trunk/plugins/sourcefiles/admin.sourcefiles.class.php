<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminsourcefiles  {
private $idparent;
private $deleted;

public function exists($filename) {
$posts = tposts::instance();
return $posts->db->findid('title = '. dbquote($filename));
}
  
public function Reread() {
$this->deleted = array();

if (count($this->deleted) > 0) {
$deleted = implode(',', $this->deleted);
$posts->db->update("status = 'deleted'", "parent = $this->idparent and id in ($deleted)");
}
}

public function readdir($dir) {
$dir = trim($dir, DIRECTORY_SEPARATOR);
$dir = str_replace(DIRECTORY_SEPARATOR, '/', $dir);
$dir .= '/';
$realdir = litepublisher::$paths->home . str_replace('/', DIRECTORY_SEPARATOR, $dir);
if($list = glob($realdir . '*.*')) {
$found = array();
foreach ($list as $filename) {
if (is_dir($realdir . $filename) {
$found[] = $this->adddir($dir, $filename);
} else {
$found[] = $this->addfile($dir, $filename);
}
}
$this->finddeleted($dir, $found);
}
}

public function addfile($dir, $filename) {
$title = $dir . $filename;
if ($id = $this->exists($title)) return $id;

$post = tsourcefile::instance(0);
$post ->title = $title;
$post->url = "/source/$title";
$post->parent = $this->idparent;

$posts = tposts::instance();
return $posts->add($post);
}

public function finddeleted($dir, $found) {
$found = implode(',', $found);
$l = strlen($dir);
$dir = dbquote($dir);
$deleted = $posts->db->idselect("parent = $this->idparent and substr(title, 0, $l) == $dir and id not in ($found)");
if (count($deleted) > 0) array_merge($this->deleted, $deleted);
}

?>
}//class
?>