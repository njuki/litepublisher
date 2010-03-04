<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class ttickets extends titems implements iposts {

  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = dbversion;
    parent::create();
    $this->table = 'tickets';
    $this->basename = 'posts'  . DIRECTORY_SEPARATOR  . 'tickets';
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

  }//class
?>