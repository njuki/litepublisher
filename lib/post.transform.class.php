<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tposttransform  {
  public $post;
  public static $arrayprops= array('categories', 'tags', 'files');
  public static $intprops= array('id', 'idurl', 'parent', 'author', 'revision', 'icon', 'commentscount', 'pingbackscount', 'pagescount', 'view');
  public static $boolprops= array('commentsenabled', 'pingenabled');
  public static $props = array('id', 'idurl', 'parent', 'author', 'revision',
  //'created', 'modified',
  'posted',
  'title', 'title2', 'filtered', 'excerpt', 'rss', 'description', 'moretitle',
  'categories', 'tags', 'files',
  'password', 'view', 'icon',
  'status', 'commentsenabled', 'pingenabled',
  'commentscount', 'pingbackscount', 'pagescount',
  );
  
  public static function instance(tpost $post) {
    $self = getinstance(__class__);
    $self->post = $post;
    return $self;
  }
  
  public static function add(tpost $post) {
    $self = self::instance($post);
    $values = array();
    foreach (self::$props as $name) {
      $values[$name] = $self->__get($name);
    }
    $db = litepublisher::$db;
    $db->table = 'posts';
    $id = $db->add($values);
    $post->rawdb->insert_a(array(
    'id' => $id,
    'created' => sqldate(),
    'modified' => sqldate(),
    'rawcontent' => $post->data['rawcontent']
    ));
    
    $db->table = 'pages';
    foreach ($post->data['pages'] as $i => $content) {
      $db->insert_a(array('post' => $id, 'page' => $i,         'content' => $content));
    }
    
    return $id;
  }
  
  public function save() {
    $db = litepublisher::$db;
    $db->table = 'posts';
    $post = $this->post;
    $list = array();
    foreach (self::$props  As $name) {
      if ($name == 'id') continue;
      $list[] = "$name = " . $db->quote($this->__get($name));
    }
    
    $db->idupdate($post->id, implode(', ', $list));
    
    $raw = array(
    'id' => $post->id,
    'modified' => sqldate()
    );
    if (false !== $post->data['rawcontent']) $raw['rawcontent'] = $post->data['rawcontent'];
    $post->rawdb->updateassoc($raw);
    $db->table = 'pages';
    $db->iddelete($this->post->id);
    foreach ($post->data['pages'] as $i => $content) {
      $db->updateassoc(array('post' => $post->id, 'page' => $i, 'content' => $content));
    }
  }
  
  public function getassoc() {
    
  }
  
  public function setassoc(array $a) {
    foreach ($a as $name => $value) {
      $this->__set($name, $value);
    }
  }
  
  public function __get($name) {
    if (method_exists($this, $get = "get$name")) return $this->$get();
    if (in_array($name, self::$arrayprops))  return implode(',', $this->post->$name);
    if (in_array($name, self::$boolprops))  return $this->post->$name ? 1 : 0;
    return $this->post->$name;
  }
  
  public function __set($name, $value) {
    if (method_exists($this, $set = "set$name")) return $this->$set($value);
    if (in_array($name, self::$arrayprops)) {
      $this->post->data[$name] = tdatabase::str2array($value);
    } elseif (in_array($name, self::$intprops)) {
      $this->post->$name = (int) $value;
    } elseif (in_array($name, self::$boolprops)) {
      $this->post->data[$name] = $value == '1';
    } else {
      $this->post->$name = $value;
    }
  }
  
  private function getposted() {
    return sqldate($this->post->posted);
  }
  
  private function setposted($value) {
    $this->post->posted = strtotime($value);
  }
  
  private function setrevision($value) {
    $this->post->data['revision'] = $value;
  }
  
}//class
?>