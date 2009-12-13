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
return $this->autoid;
  }

public function setstatus($id, $approve) {
if (isset($this->items[$id]) && ($approve != $this->items[$id]['approved'])) {
$this->items[$id]['approved'] = $approve;
$this->save();
$approved = 0;
foreach ($this->items as $id) {
if ($item['approved']) $approved++;
}

$post = tpost::instance($this->pid);
$post->pingbackscount = $approved;
$post->save();
$post->clearcache();
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