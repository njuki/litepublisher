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
  public static $boolprops= array('commentsenabled', 'pingenabled', 'rssenabled');
  public static $props = array('id', 'idurl', 'parent', 'author',
  //'created', 'modified',
'posted',
  'title', 'filtered', 'excerpt', 'rss', 'description', 'moretitle',
  'categories', 'tags', 'files',
  'password', 'template', 'subtheme', 'icon',
  'status', 'commentsenabled', 'pingenabled', 'rssenabled',
  'commentscount', 'pagescount',
  );

  public static function instance(tpost $post) {
    $self = getinstance(__class__);
    $self->post = $post;
    return $self;
  }

  public static function add(tpost $post) {
    global $db;
$self = self::instance($post);
    $db->table = 'posts';
    $names =implode(', ', self::$props);
    $values = array();
    foreach (self::$props as $name) {
      $values[] = $db->quote($self->__get($name));
    }
    
    $id = $db->insertrow("($names) values (" . implode(', ', $values) . ')');

$self->post->rawdb->add(array(
'id' => $id,
'created' => sqldate(),
'modified' => sqldate(),
'rawcontent' => $self->post->data['rawcontent']
));

$db->table = 'pages';
     foreach ($self->post->data['pages'] as $i => $content) {
$db->add(array('post' => $id, 'page' => $i,         'content' => $content));
      }

return $id;
  }
  
  public function save() {
    global $db;
    $db->table = 'posts';
    $list = array();
    foreach (self::$props  As $name) {
      $list[] = "$Name = " . $db->quote($this->__get($name));
    }
    
    $db->idupdate($this->post->id, implode(', ', $list));

$this->post->rawdb->updateassoc(array(
'id' => $this->post->id,
'modified' => sqldate(),
'rawcontent' => $this->post->data['rawcontent']
));

$db->table = 'pages';
$db->iddelete($this->post->id);
     foreach ($this->post->data['pages'] as $i => $content) {
$db->updateassoc(array('post' => $this->post->id, 'page' => $i, 'content' => $content));
      }
  }
  
  public function __get($name) {
    if (method_exists($this, $get = "get$name")) return $this->$get();
    if (in_array($name, self::$arrayprops))  return implode(',', $this->post->$name);
    if (in_array($name, self::$boolprops))  return $this->post->$name ? 'true' : 'false';
    return $this->post->$name;
  }
  
  public function __set($name, $value) {
    if (method_exists($this, $set = "set$name")) return $this->$set($value);
    if (in_array($name, self::$arrayprops)) {
$list = explode(',', $value);
foreach ($list as $i => $value) $list[$i] = (int) trim($value);
    $this->post->$name = $list;
    } elseif (in_array($name, self::$boolprops)) {
      $this->post->$name = $value == '1';
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
  
}//class
?>