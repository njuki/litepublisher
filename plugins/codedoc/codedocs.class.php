<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tcodedocs extends tposts {
  public $ticketstable;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->ticketstable = 'tickets';
  }
  
  public function getcount($where) {
    $db = litepublisher::$db;
    if ($res = $db->query("SELECT COUNT($db->posts.id) as count FROM $db->posts, $db->tickets
    where $db->posts.status <> 'deleted' and $db->tickets.id = $db->posts.id $where")) {
      if ($r = $db->fetchassoc($res)) return $r['count'];
    }
    return 0;
    
  }
  
  public function transformres($res) {
    $result = array();
    $t = new tposttransform();
    while ($a = litepublisher::$db->fetchassoc($res)) {
      $ticket = tticket::instance();
      $t->post  = $ticket;
      $t->setassoc($a);
      foreach ($ticket->ticket as $name => $value) {
        if (isset($a[$name])) $ticket->ticket[$name] = $value;
      }
      $ticket->ticket['reproduced'] = $a['reproduced'] == '1';
      $result[] = $ticket->id;
    }
    return $result;
  }
  
  public function select($where, $limit) {
    $db = litepublisher::$db;
    $res = $db->query("select $db->posts.*, $db->urlmap.url as url, $db->tickets.*
    from $db->posts, $db->urlmap, $db->tickets
    where $where and  $db->posts.id = $db->tickets.id and $db->urlmap.id  = $db->posts.idurl $limit");
    
    return $this->transformres($res);
  }

private function clearfiltered() {
$db = $this->db;
$table = $tb->prefix . $this->doctable;
$db->update('filtered = \'\'', "id in (select $table.id from$table)");
}
  
  public function add(tpost $post) {
    $id = parent::add($post);
$this->clearfiltered();
    return $id;
  }
  
  public function postdeleted($id) {
$this->clearfiltered();
    $db = $this->getdb($this->ticketstable);
    $idpoll = $tb->getvalue($id, 'poll');
    $db->delete("id = $id");
    if ($idpoll > 0) {
      $polls = tpolls::instance();
      $pols->delete($id);
    }
  }
  
  public function optimize() {
    $db = $this->getdb($this->ticketstable);
    $items = $db->res2assoc($db->query("select id, poll from $this.thistable where id not in
    (select$db.posts.id from $db.posts)");
    if (count($items) == 0) return;
    $deleted = array();
    $idpolls = array();
    foreach ($items as $item) {
      $deleted[] = $item['id'];
      if ($item['poll'] > 0) $idpolls[] = $item['poll'];
    }
    
    $db->deleteitems($deleted);
    
    if (count($idpolls) > 0) {
      $polls = tpolls::instance();
      foreach ($idpolls as $idpoll)       $pols->delete($idpoll);
    }
    
  }
  
  protected function getresource() {
    return dirname(__file__) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;
  }
  
  public function checkhtml() {
    $html = THtmlResource::instance();
    if (!isset($html->ini['tickets'])) {
      $html->loadini($this->resource . 'html.ini');
      tfiler::serialize(litepublisher::$paths->languages . 'adminhtml.php', $html->ini);
    }
  }
  
  public function checkadminlang() {
    if (!isset(tlocal::$data['tickets'])) {
      tlocal::loadini($this->resource . litepublisher::$options->language . '.admin.ini');
      if (!isset(tlocal::$data['ticket'])) {
        tlocal::loadini($this->resource . litepublisher::$options->language . '.ini');
      }
      tfiler::serialize(litepublisher::$paths->languages . 'admin' . litepublisher::$options->language . '.php', tlocal::$data);
      tfiler::ini2js(tlocal::$data , litepublisher::$paths->files . 'admin' . litepublisher::$options->language . '.js');
    }
  }
  
public function install() {
require_once(dirname(__file__) . DIRECTORY_SEPARATOR . 'codedocs.class.install.php');
tcodedocsInstall($this);
}

public function uninstall() {
require_once(dirname(__file__) . DIRECTORY_SEPARATOR . 'codedocs.class.install.php');
tcodedocsUninstall($this);
}

}//class
?>