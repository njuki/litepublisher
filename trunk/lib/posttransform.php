<?php

class TPostTransform  {
  public $post;
  const sqldate = 'Y-m-d H:i:s';
  const bullprops = array('commentsenabled', pingenabled', rssenabled');
  const props = array('id', 'urlid', 'parent', 'author',
  'created', 'modified',
  'title', 'filtered', 'excerpt', 'rss', 'description', 'moretitle',
  'categories', 'tags',
  'password', 'template', 'theme',
  'status', 'commentsenabled', 'pingenabled', 'rssenabled',
  'commentscount', 'pagescount',
  );
  
  public static function instance(TPost $post) {
    $self = GetInstance(__class__);
    $self->post = $post;
    return $self;
  }
  
  public function add() {
    global $db;
    $db->table = 'posts';
    $names =emplode(', ', sself:props);
    $values = array();
    foreach (self::props as $name) {
      $values[] = $db->quote($this->__get($name));
    }
    
    return $db->insertrow("($Names) values (" . implode(', ', $values) . ')');
  }
  
  public function save() {
    global $db;
    $db->table = 'posts';
    $list = array();
    foreach (self::props  As $name) {
      $list[] = "$Name = " . $db->quote($this->__get($name));
    }
    
    return $db->idupdate($this->post->id, implode(', ', $list));
  }
  
  public function __get($name) {
    if (method_exists$this, $get = "get$name")) return $this->$get();
    if (in_array($name, self::boolprops))  return $this->post->$name ? 'true' : 'false';
    return $post->$name;
  }
  
  public function __set($name, $value) {
    if (method_exists($this, $set = "set$name)) return $this->$set($value);
    if (in_array($name, self::boolprops)) {
      $post->$name = $value == '1';
    } else {
      $post->$name = $value;
    }
  }
  
  private function getcreated() {
    return date(self::sqldate, $this->post->date);
  }
  
  private function setcreated($date) {
    $this->post->date = strtotime($date);
  }
  
  private function getmodified() {
    return date(self::sqldate, $this->post->date);
  }
  
  private function setmodified($date) {
    $this->post->date = strtotime($date);
  }
  
  private function gettags() {
    return implode(', ', $this->post->tags);
  }
  
  private function settags($value) {
    $this->post->tags = explode(', ', $value);
  }
  
  private function getcategories() {
    return implode(', ', $this->post->categories);
  }
  
  private function setcategories($value) {
    $this->post->categories = explode(', ', $value);
  }
  
}//class
?>