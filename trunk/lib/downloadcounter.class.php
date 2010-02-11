<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tdownloadcounter extends TItems {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->cache = false;
    $this->basename = 'downloadcounter';
    $this->table = 'downloadcounter';
  }
  
  public function reqest($args) {
    if (!isset($_GET['id']))  return 404;
    $id = (int) $_GET['fileid'];
    $files = tfiles::instance();
    if (!$files->itemexists($id)) return 404;
    if (dbversion) {
      if ($count = $this->db->getvalue($id, 'downloaded')) {
        $count++;
        $this->db->setvalue($id, 'downloaded', $count);
      } else {
        $this->db->add(array(
        'id' => $id,
        'downloaded' => 1
        ));
      }
    } else {
      if (!isset($this->items[$id])) {
        $this->items[$id] = 1;
      } else {
        $this->items[$id]++;
      }
      $this->save();
    }
    
    $url = $files->geturl($id);
    $urlmap->redir301($url);
  }
  
}//class

?>