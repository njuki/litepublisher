<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tpostprops extends tbasepostprops {

  public static function i() {
    return getinstance(__class__);
  }
  
protected function create() {
parent::create();
$this->dataname = 'post';
$this->table = 'posts';
$this->defvalues = array(
    'id' => 0,
    'idview' => 1,
    'idurl' => 0,
    'parent' => 0,
    'author' => 0,
    'revision' => 0,
    'icon' => 0,
    'idperm' => 0,
    'class' => 'tpost',,
    'posted' => 0,
    'modified' => 0,
    'url' => '',
    'title' => '',
    'title2' => '',
    'filtered' => '',
    'excerpt' => '',
    'rss' => '',
    'rawcontent' => false,
    'keywords' => '',
    'description' => '',
    'head' => '',
    'moretitle' => '',
    'categories' => array(),
    'tags' => array(),
    'files' => array(),
    'status' => 'published',
    'comstatus' => litepublisher::$options->comstatus,
    'pingenabled' => litepublisher::$options->pingenabled,
    'password' => '',
    'commentscount' => 0,
    'pingbackscount' => 0,
    'pagescount' => 0,
    );
    
$this->intarray = array('categories', 'tags', 'files');
$this->intprops= array('id', 'idurl', 'parent', 'author', 'revision', 'icon', 'commentscount', 'pingbackscount', 'pagescount', 'idview', 'idperm');
$this->boolprops= array('pingenabled');
$this->datetimeprops = array('posted');
}

}//class

class tpostpages extends tbasepostprops {

  public static function i() {
    return getinstance(__class__);
  }
  
protected function create() {
parent::create();
$this->dataname = 'pages';
$this->table = 'pages';
$this->defvalues = array(
    'id' => 0,
'page' => 0,
'content' => ''
);
$this->intprops = array('id', 'page');
}

public function getpages(tpost $post) {
}

public function setpages(tpost $post, array $pages) {
}

public function add(tpost $post) {
$db = $this->db;
    foreach ($post->syncdata[$this->dataname] as $page => $content) {
      $db->insert_a(array(
'id' => $post->id,
 'page' => $page,
         'content' => $content
));
    }
}

public function save(tpost $post) {
$this->db->iddelete($post->id);
$this->add($post);
}

  public function addpage(tpost $post, $s) {
    $post->syncdata[$this->datename][] = $s;
    $post->pagescount = count($post->syncdata[$this->dataname]);
    if ($post->id > 0) {
      $this->db->insert_a(array(
      'id' => $post->id,
      'page' => $post->pagescount -1,
      'content' => $s
      ));
    }
  }
  
  public function delete(tpost $post) {
    $post->syncdata[$this->dataname] = array();
    $post->pagescount = 0;
    if ($post->id > 0) $this->db->iddelete($post->id);
  }
  

  public function getpage(tpost $post, $i) {
if (!isset($post->propdata[$this->dataname])  $post->propdata[$this->dataname] = array();
$data = &$post->propdata[$this->dataname];
    if ( isset($data[$i]))   return $data[$i];
    if ($post->id > 0) {
      if ($r = $this->db->getassoc("(id = $post->id) and (page = $i) limit 1")) {
        $s = $r['content'];
      } else {
        $s = false;
      }
$data[$i] = $s;
      return $s;
    }
    return false;
  }
  
}//class