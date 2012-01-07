<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tcomusers extends titems {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = dbversion;
    parent::create();
    $this->table = 'comusers';
    $this->basename = 'comusers';
  }
  
  public function add($name, $email, $url, $ip) {
$email = strtolower(trim($email));
    if ($id = $this->find($name, $email, $url)) {
      $this->db->setvalue($id, 'ip', $ip);
      return $id;
    }
    
    if (($parsed = @parse_url($url)) &&  is_array($parsed) ) {
      if ( empty($parsed['host'])) {
        $url = '';
      } else {
        if ( !isset($parsed['scheme']) || !in_array($parsed['scheme'], array('http','https')) ) $parsed['scheme']= 'http';
        if (!isset($parsed['path'])) $parsed['path'] = '';
        $url = $parsed['scheme'] . '://' . $parsed['host'] . $parsed['path'];
        if (!empty($parsed['query'])) $url .= '?' . $parsed['query'];
      }
    } else {
      $url = '';
    }
    $id = $this->db->add(array(
    'trust' => 0,
    'name' => $name,
    'url' => $url,
    'email' => $email,
    'ip' => $ip,
    'cookie' => md5uniq(),
    ));
    
    litepublisher::$classes->commentmanager->authoradded($id);
    return $id;
  }
  
  public function edit($id, $name, $url, $email, $ip) {
    $this->db->UpdateAssoc(array(
    'id' => $id,
    'name' => $name,
    'email' => strtolower(trim($email)),
    'url' => $url,
    'ip' => $ip
    ));
    $manager = tcommentmanager::i();
    $manager->authoredited($id);
  }
  
  public function delete($id) {
    parent::delete($id);
    $manager = tcommentmanager::i();
    $manager->authordeleted($id);
  }
  
  public function fromcookie($cookie) {
    return $this->db->finditem('cookie = '. dbquote($cookie));
  }
  
  public function getcookie($id) {
    return $this->getvalue($id, 'cookie');
  }
  
  public function find($name, $email, $url) {
    $id = $this->db->findid('name = '. dbquote($name) . ' and email = '. dbquote($email) .' and url = '. dbquote($url));
    return $id == '0' ? false : (int) $id;
  }
  
  public function request($arg) {
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 1;
    if (!$this->itemexists($id)) return "<?php turlmap::redir301('/');";;
    $item = $this->getitem($id);
    $url = $item['url'];
    if (!strpos($url, '.')) $url = litepublisher::$site->url . litepublisher::$site->home;
    if (!strbegin($url, 'http://')) $url = 'http://' . $url;
    return "<?php turlmap::redir('$url');";
  }
  
}//class

?>