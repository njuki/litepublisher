<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class ttickets extends tposts {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->childtable = 'tickets';
  }
  
  public function newpost() {
    return tticket::instance();
  }
  
  public function createpoll() {
    tlocal::loadsection('admin', 'tickets', dirname(__file__) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR);
    $lang = tlocal::instance('tickets');
    $items = explode(',', $lang->pollitems);
    $polls = tpolls::instance();
    return $polls->add('', 'opened', 'button', $items);
  }
  
  public function add(tpost $post) {
    $post->poll = $this->createpoll();
    $post->updatefiltered();
    //$post->status = 'draft';
    $id = parent::add($post);
    $this->notify($post);
    return $id;
  }
  
  private function notify(tticket $ticket) {
    ttheme::$vars['ticket'] = $ticket;
    $args = targs::instance();
    $args->adminurl = litepublisher::$site->url . '/admin/tickets/editor/'. litepublisher::$site->q . 'id=' . $ticket->id;
    $mailtemplate = tmailtemplate::instance('tickets');
    $subject = $mailtemplate->subject($args);
    $body = $mailtemplate->body($args);
    tmailer::sendtoadmin($subject, $body);
  }
  
  public function edit(tpost $post) {
    $post->updatefiltered();
    return parent::edit($post);
  }
  
  public function postdeleted($id) {
    $db = $this->getdb($this->childtable);
    $idpoll = $db->getvalue($id, 'poll');
    $db->delete("id = $id");
    if ($idpoll > 0) {
      $polls = tpolls::instance();
      $polls->delete($idpoll);
    }
  }
  
  public function deletechilds(array $items) {
    $deleted = implode(',', $items);
    $db = $this->getdb($this->childtable);
    $idpolls = $db->res2id($db->query("select poll from $db->prefix$this->childtable where (id in ($deleted)) and (poll  > 0)"));
    if (count ($idpolls) > 0) {
      $polls = tpolls::instance();
      foreach ($idpolls as $idpoll)       $pols->delete($idpoll);
    }
  }
  
  public function hasright($who, $group) {
    return ($who == 'ticket') &&($group == 'author');
  }
  
}//class
?>