<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadmintickets extends tadminmenu {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function gethtml($name = '') {
    $lang = tlocal::admin('tickets');
    $lang->ini['tickets'] = $lang->ini['ticket'] + $lang->ini['tickets'];
    return parent::gethtml($name);
  }
  
  protected function getlogoutlink() {
    return $this->gethtml('login')->logout();
  }
  
  public function getcontent() {
    $result = $this->logoutlink;
    $tickets = ttickets::i();
    $perpage = 20;
    $where = litepublisher::$options->group == 'ticket' ? ' and author = ' . litepublisher::$options->user : '';
    
    switch ($this->name) {
      case 'opened':
      $where .= " and state = 'opened' ";
      break;
      
      case 'fixed':
      $where .= " and state = 'fixed' ";
      break;
    }
    
    $count = $tickets->getchildscount($where);
    $from = $this->getfrom($perpage, $count);
    
    if ($count > 0) {
      $items = $tickets->select("status <> 'deleted' $where", " order by posted desc limit $from, $perpage");
      if (!$items) $items = array();
    }  else {
      $items = array();
    }
    
    $html = $this->html;
    $result .=$html->getitemscount($from, $from + count($items), $count);
    $lang = tlocal::admin('tickets');
    ttheme::$vars['ticket_status'] = new ticket_status();
    $table = $html->tableposts($items, array(
    array('center', $lang->date, '$post.date'),
    array('left', $lang->posttitle, '$post.bookmark'),
    array('left', $lang->author, '$post.authorlink'),
    array('left', $lang->status, '$ticket_status.status'),
    array('left', $lang->category, '$post.category'),
    array('left', $lang->state, '$ticket_status.state'),
    array('center', $lang->edit, '<a href="' . tadminhtml::getadminlink('/admin/tickets/editor/', 'id') . '=$post.id">' . $lang->edit . '</a>'),
    ));
    
    unset(ttheme::$vars['ticket_status']);
    //wrap form
    if (litepublisher::$options->group != 'ticket') {
      $args = new targs();
      $args->table = $table;
      $result .= $html->tableform ($args);
    } else {
      $result .= $table;
    }
    
    $result = $html->fixquote($result);
    
    $theme = ttheme::i();
    $result .= $theme->getpages($this->url, litepublisher::$urlmap->page, ceil($count/$perpage));
    return $result;
  }
  
  public function processform() {
    if (litepublisher::$options->group == 'ticket') return '';
    $tickets = ttickets::i();
    $status = isset($_POST['publish']) ? 'published' :
    (isset($_POST['setdraft']) ? 'draft' :
    (isset($_POST['setfixed']) ? 'fixed' :'delete'));
    foreach ($_POST as $key => $id) {
      if (!is_numeric($id))  continue;
      $id = (int) $id;
      if ($status == 'delete') {
        $tickets->delete($id);
      } else {
        $ticket = tticket::i($id);
        if ($status == 'fixed') {
          $ticket->set_state($status);
        } else {
          $ticket->status = $status;
        }
        $tickets->edit($ticket);
      }
    }
  }
  
}//class

class ticket_status {
  
  public function __get($name) {
    $ticket = ttheme::$vars['post'];
    $lang = tlocal::i();
    switch ($name) {
      case 'status':
    return $lang->{$ticket->status};
      
      case 'prio':
    return $lang->{$ticket->prio};
      
      case 'state':
    return $lang->{$ticket->state};
    }
    return '';
  }
  
}//class