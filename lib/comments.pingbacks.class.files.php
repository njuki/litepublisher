<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tpingbacks extends tabstractpingbacks implements ipingbacks {
  private static $instances;
  
  public static function instance($pid) {
    global $classes;
    if (!isset(self::$instances)) self::$instances = array();
    if (isset(self::$instances[$pid]))       return self::$instances[$pid];
    $self = $classes->newinstance(__class__);
    self::$instances[$pid]  = $self;
    $self->pid = $pid;
    $self->load();
    return $self;
  }
  
  public function getbasename() {
    return 'posts'.  DIRECTORY_SEPARATOR . $this->pid . DIRECTORY_SEPARATOR . 'comments.pingbacks';
  }
  
  public function doadd($url, $title) {
    $this->items[++$this->autoid] = array(
    'url' => $url,
    'title' => $title,
    'posted' => time(),
    'ip' => preg_replace( '/[^0-9., ]/', '',$_SERVER['REMOTE_ADDR']),
    'approved' => false
    );
    $this->save();
    $this->updatecount();
    return $this->autoid;
  }
  
  private function updatecount() {
    $count= 0;
    foreach ($this->items as $id => $item) {
      if ($item['approved']) $count++;
    }
    
    $post = tpost::instance($this->pid);
    $post->pingbackscount = $count;
    $post->save();
    $post->clearcache();
  }
  
  public function edit($id, $title, $url) {
    if (isset($this->items[$id])) {
      $this->items[$id]['title'] = $title;
      $this->items[$id]['url'] = $url;
      $this->save();
    }
  }
  
  public function exists($url) {
    foreach ($this->items as $id => $item) {
      if ($url == $item['url']) return $id;
    }
    return false;
  }
  
  public function setstatus($id, $approve) {
    if (isset($this->items[$id]) && ($approve != $this->items[$id]['approved'])) {
      $this->items[$id]['approved'] = $approve;
      $this->save();
      
      $this->updatecount();
    }
  }
  
  public function getcontent() {
    global $pingback;
    $result = '';
    $a = array();
    $pingback = new tarray2props($a);
    $lang = tlocal::instance('comment');
    $theme = ttheme::instance();
    $tml = $theme->content->post->templatecomments->pingbacks->pingback;
    foreach ($this->items as $url => $item) {
      if (!$item['approved']) continue;
      $pingback->id = $id;
      $pingback->url = $item['url'];
      $pingback->title = $item['title'];
      $result .= $theme->parse($tml);
    }
    return sprintf($theme->content->post->templatecomments->pingbacks, $result);
  }
  
}//class

?>