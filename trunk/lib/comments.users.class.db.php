<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tcomusers extends titems {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = dbversion;
    parent::create();
    $this->table = 'comusers';
    $this->cache = false;
  }
  
  public function add($name, $email, $url) {
    $ip = preg_replace( '/[^0-9., ]/', '',$_SERVER['REMOTE_ADDR']);
    if ($id = $this->find($name, $email, $url)) return $id;
    $id = $this->db->add(array(
    'trust' => 0,
    'name' => $name,
    'url' => $url,
    'email' => $email,
    'cookie' => md5uniq(),
    ));
    
    $manager = tcommentmanager::instance();
    $manager->authoradded($id);
    return $id;
  }
  
  public function edit($id, $name, $url, $email, $ip) {
    $this->UpdateAssoc(array(
    'name' => $name,
    'email' => $email,
    'url' => $url
    ));
    $manager = tcommentmanager::instance();
    $manager->authoredited($id);
  }
  
  public function delete($id) {
    parent::delete($id);
    $manager = tcommentmanager::instance();
    $manager->authordeleted($id);
  }
  
  public function fromcookie($cookie) {
    return $this->db->finditem('cookie = '. dbquote($cookie));
  }
  
  public function getcookie($id) {
    return $this->getvalue($id, 'cookie');
  }
  
  public function find($name, $email, $url) {
    return $this->db->findid('name = '. dbquote($name) . ' and email = '. dbquote($email) .' and url = '. dbquote($url));
  }
  
  public function request($arg) {
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 1;
if (!$this->itemexists($id)) return 404;
      $item = $this->getitem($id);
    $url = $item['url'];
    if (!strpos($url, '.')) $url = litepublisher::$options->url . litepublisher::$options->home;
    if (!strbegin($url, 'http://')) $url = 'http://' . $url;
    turlmap::redir($url);
  }
  
}//class

?>