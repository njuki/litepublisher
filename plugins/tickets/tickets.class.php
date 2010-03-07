<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class ttickets extends tposts {
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
  
  public function add(tpost $post) {
    $post->status = 'draft';
    $id = parent::add($post);
    $this->notify($post);
    return $id;
  }
  
  private function notify(tticket $ticket) {
    ttheme::$vars['ticket'] = $ticket;
    $args = targs::instance();
    $args->adminurl = litepublisher::$options->url . '/admin/tickets/editor/'. litepublisher::$options->q . 'id=' . $ticket->id;
    $mailtemplate = tmailtemplate::instance('tickets');
    $subject = $mailtemplate->subject($args);
    $body = $mailtemplate->body($args);
    tmailer::sendtoadmin($subject, $body);
  }
  
  public function postdeleted($id) {
    $db = $this->getdb($this->ticketstable);
    $idpoll = $tb->getvalue($id, 'poll');
    $db->delete("id = $id");
    if ($idpoll > 0) {
      $polls = tpolls::instance();
      $pols->delete($id);
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
  
}//class
?>