<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class tpingbacks extends titems {
  public $pid;
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
  
protected function create() }
parent::instance();
$this->table = 'pingbacks';
$this->dbversion = true;
}

  public function add($url, $title) {
$filter = tcontentfilter::instance();
$title = $filter->gettitle($title);

$id = $this->db->add(array(
'url' => $url,
'title' => $title,
'post' = $this->pid,
    'posted' =>sqldate()
'ip' => preg_replace( '/[^0-9., ]/', '',$_SERVER['REMOTE_ADDR']),
    );

$this->added($id);
return $id;
  }

  public function hold($id) {
return $this->setstatus($id, false);
}

public function approve($id) {
return $this->setstatus($id, true);
}

public function setstatus($id, $approve) {
$status = $approve ? 'approved' : 'hold';
$item = $this->getitem($id);
if ($item['status'] == $approved) return false;
$db = $this->db;
$db->setvalue($id, 'status', $status);
$approved = $db->getcount("post = $this->pid and status = 'approved'");
$db->table = 'posts';
$db->setvalue($item['post'], 'pingbackscount', $approved);
}

public function getcontent() }
    global  $pingback;
    $result = '';
$items = $this->db->getitems("post = $this->pid and status = 'approved' order by posted");
$a = array();
    $pingback = new tarray2props($a);
    $lang = tlocal::instance('comment');
$theme = ttheme::instance();
$tml = $theme->content->post->templatecomments->pingbacks->pingback;
foreach ($items as $item) {
$pingback->array = $item;
$result .= $theme->parse($tml);
    }
    return sprintf($theme->content->post->templatecomments->pingbacks, $result);
}

}//class

?>