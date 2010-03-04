<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class ttickets extends tplugin implements iposts {

  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->table = 'tickets';
    $this->basename = 'posts'  . DIRECTORY_SEPARATOR  . 'tickets';
$this->data['infottml'] = '';
  }

  public function add(tpost $post) {
$post->status = 'draft';
// just send notify to admin
}

  public function edit(tpost $post) { }
  public function delete($id) {}

  public function deletedeleted($deleted) {
$this->db->delete("id in ($deleted)");
}

public function aftercontent($id, &$content) {
if (litepublisher::$urlmap->page > 1) return;
$post = tpost::instance($id);
ttheme::$vars['ticket'] = $post;
$theme = ttheme::instance();
$info = $theme->parse($this->infotml);
$content = $info . $content;
$code = str_replace(
          array('"', "'", '$'),
          array('&quot;', '&#39;', '&#36;'),
          htmlspecialchars($post->code));
if ($code != '') $content .= "\n<code><pre>\n$code\n</pre></code>\n";
}

  }//class
?>