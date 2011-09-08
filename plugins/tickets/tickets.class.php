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
    $lang = tlocal::admin('tickets');
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
  
  public function postsdeleted(array $items) {
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
  
  public function onexclude($id) {
    if (litepublisher::$options->group == 'ticket') {
      $admin = tadminmenus::instance();
      return $admin->items[$id]['url'] == '/admin/posts/';
    }
    return false;
  }
  
}//class