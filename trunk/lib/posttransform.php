<?php

class TPostTransform  {
  public $post;
  const bullprops = array('commentsenabled', pingenabled', rssenabled');
  const props = array('id', 'idurl', 'parent', 'author',
  //'created', 'modified',
'posted',
  'title', 'filtered', 'excerpt', 'rss', 'description', 'moretitle',
  'categories', 'tags',
  'password', 'template', 'subtheme', 'icon',
  'status', 'commentsenabled', 'pingenabled', 'rssenabled',
  'commentscount', 'pagescount',
  );
  
  public static function instance(TPost $post) {
    $self = getinstance(__class__);
    $self->post = $post;
    return $self;
  }
  
  public static function add(tpost $post) {
    global $db;
$self = self::instance($post);
    $db->table = 'posts';
    $names =emplode(', ', sself:props);
    $values = array();
    foreach (self::props as $name) {
      $values[] = $db->quote($self->__get($name));
    }
    
    $id = $db->insertrow("($Names) values (" . implode(', ', $values) . ')');

$self->post->rawdb->add(array(
'id' => $id,
'created' => sqldate(),
'modified' => sqldate(),
'rawcontent' => $this->post->data['rawcontent']
));

$db->table = 'pages';
     foreach ($self->post->data['pages'] as $i => $content) {
$db->add(array('post' => $id, 'page' => $i         'content' => $content));
      }

return $id;
  }
  
  public function save() {
    global $db;
    $db->table = 'posts';
    $list = array();
    foreach (self::props  As $name) {
      $list[] = "$Name = " . $db->quote($this->__get($name));
    }
    
    $db->idupdate($this->post->id, implode(', ', $list));

$this->post->rawdb->updateassoc(array(
'id' => $this->post->id,
'modified' => sqldate(),
'rawcontent' => $this->post->data['rawcontent']
));

$db->table = 'pages';
$db->delete("id = '. $this->post->id);
     foreach ($this->post->data['pages'] as $i => $content) {
$db->updateassoc(array('post' => $this->post->id, 'page' => $i         'content' => $content));
      }
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

  private function getposted() {
    return sqldate($this->post->posted);
  }
  
  private function setposted($date) {
    $this->post->posted = strtotime($date);
  }
  
  
  private function getcreated() {
    return sqldate($this->post->date);
  }
  
  private function setcreated($date) {
    $this->post->date = strtotime($date);
  }
  
  private function getmodified() {
    return sqldate( $this->post->date);
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