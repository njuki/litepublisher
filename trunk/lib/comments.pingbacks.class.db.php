<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class tpingbacks extends tabstractpingbacks implements ipingbacks {

  public static function instance($pid) {
$result = getinstance(__class__);
$result->pid = $pid;
return $result;
}

protected function create() {
parent::create();
$this->table = 'pingbacks';
$this->dbversion = true;
}

  public function doadd($url, $title) {
return $this->db->add(array(
'url' => $url,
'title' => $title,
'post' => $this->pid,
    'posted' =>sqldate(),
'ip' => preg_replace( '/[^0-9., ]/', '',$_SERVER['REMOTE_ADDR'])
    ));
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

public function postdeleted($idpost) {
$this->db->delete("post = $idpost");
}

public function getcontent() {
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