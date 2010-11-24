<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadmintickets extends tadminmenu {
  
  public static function instance($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function gethtml($name = '') {
    $html = tadminhtml::instance();
$dir = dirname(__file__) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;
$html->addini('tickets', $dir . 'html.ini');
tlocal::loadsection('', 'ticket', $dir);
tlocal::loadsection('admin', 'tickets', $dir);
tlocal::$data['tickets'] = tlocal::$data['ticket'] + tlocal::$data['tickets'];
    return parent::gethtml($name);
  }
  
  protected function getlogoutlink() {
    return $this->gethtml('login')->logout();
  }
  
  public function getcontent() {
    $result = $this->logoutlink;
    $tickets = ttickets::instance();
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
    $result .=sprintf($html->h2->count, $from, $from + count($items), $count);
    $result .= $html->listhead();
    $args = targs::instance();
    $args->adminurl = $this->adminurl;
    $args->editurl = tadminhtml::getadminlink('/admin/tickets/editor/', 'id');
    $lang = tlocal::instance('tickets');
    foreach ($items  as $id ) {
      $ticket = tticket::instance($id);
      ttheme::$vars['ticket'] = $ticket;
    $args->status = $lang->{$ticket->status};
      $args->type = tlocal::$data['ticket'][$ticket->type];
      $args->prio = tlocal::$data['ticket'][$ticket->prio];
      $args->state = tlocal::$data['ticket'][$ticket->state];
      $result .= $html->itemlist($args);
    }
    $result .= $html->footer();
    if (litepublisher::$options->group != 'ticket') {
      $result  = "<form name='form' action='' method='post'>" . $result;
      $result .= $html->listfooter();
    }
    $result = $html->fixquote($result);
    
    $theme = ttheme::instance();
    $result .= $theme->getpages($this->url, litepublisher::$urlmap->page, ceil($count/$perpage));
    return $result;
  }
  
  public function processform() {
    if (litepublisher::$options->group == 'ticket') return '';
    $tickets = ttickets::instance();
    $status = isset($_POST['publish']) ? 'published' :
    (isset($_POST['setdraft']) ? 'draft' :
    (isset($_POST['setfixed']) ? 'fixed' :'delete'));
    foreach ($_POST as $key => $id) {
      if (!is_numeric($id))  continue;
      $id = (int) $id;
      if ($status == 'delete') {
        $tickets->delete($id);
      } else {
        $ticket = tticket::instance($id);
        if ($status == 'fixed') {
          $ticket->state = $status;
        } else {
          $ticket->status = $status;
        }
        $tickets->edit($ticket);
      }
    }
  }
  
}//class
?>