<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tticketeditor extends tposteditor {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function request($id) {
    if ($s = parent::request($id)) return $s;
    $this->basename = 'tickets';
    if ($this->idpost > 0) {
      $ticket = tticket::instance($this->idpost);
      if ((litepublisher::$options->group == 'ticket') && (litepublisher::$options->user != $ticket->author)) return 404;
    }
  }
  
  public function gethtml($name = '') {
    $tickets = ttickets::instance();
    $tickets->checkhtml();
    $tickets->checkadminlang();
    return parent::gethtml($name);
  }
  
  protected function getlogoutlink() {
    return $this->gethtml('login')->logout();
  }
  
  public function getcontent() {
    $result = $this->logoutlink;
    $this->basename = 'tickets';
    $html = $this->html;
    $ticket = tticket::instance($this->idpost);
    ttheme::$vars['ticket'] = $ticket;
    $args = targs::instance();
    if ($ticket->id > 0) $result .= $html->headeditor ();
    $args->categories = $this->getcategories($ticket);
    $args->raw = $ticket->rawcontent;
    $args->code = $ticket->code;
    $args->fixed = $ticket->state == 'fixed';
    $types = array(
    'bug' => tlocal::$data['ticket']['bug'],
    'feature' => tlocal::$data['ticket']['feature'],
    'support' => tlocal::$data['ticket']['support'],
    'task' => tlocal::$data['ticket']['task'],
    );
    
    $args->typecombo= $html->array2combo($types, $ticket->type);
    $args->typedisabled = $ticket->id == 0 ? '' : "disabled = 'disabled'";
    
    $states =array();
    foreach (array('fixed', 'opened', 'wontfix', 'invalid', 'duplicate', 'reassign') as $state) {
      $states[$state] = tlocal::$data['ticket'][$state];
    }
    $args->statecombo= $html->array2combo($states, $ticket->state);
    
    $prio = array();
    foreach (array('trivial', 'minor', 'major', 'critical', 'blocker') as $p) {
      $prio[$p] = tlocal::$data['ticket'][$p];
    }
    $args->priocombo = $html->array2combo($prio, $ticket->prio);
    
    $result .= $html->editor($args);
    $result = $html->fixquote($result);
    return $result;
  }
  
  public function processform() {
    extract($_POST);
    $tickets = ttickets::instance();
    $this->basename = 'tickets';
    $html = $this->html;
    
    // check spam
    if ($id == 0) {
      $newstatus = 'published';
      if (litepublisher::$options->group == 'ticket') {
        $hold = $tickets->db->getcount('status = \'draft\' and author = '. litepublisher::$options->user);
        $approved = $tickets->db->getcount('status = \'published\' and author = '. litepublisher::$options->user);
        if ($approved < 3) {
          if ($hold - $approved >= 1) return $html->h2->noapproved;
          $newstatus = 'draft';
        }
      }
    }
    
    if (empty($title)) return $html->h2->emptytitle;
    $ticket = tticket::instance((int)$id);
    $ticket->title = $title;
    $ticket->categories = $this->getcats();
    $ticket->tagnames = $tags;
    if ($ticket->author == 0) $ticket->author = litepublisher::$options->user;
    if (isset($icon)) $ticket->icon = (int) $icon;
    if (isset($fileschanged))  $ticket->files = $this->getfiles();
    $ticket->content = $raw;
    $ticket->code = $code;
    $ticket->prio = $prio;
    $ticket->state = $state;
    $ticket->version = $version;
    $ticket->os = $os;
    if (litepublisher::$options->group != 'ticket') $ticket->state = $state;
    if ($id == 0) {
      $ticket->status = $newstatus;
      $ticket->type = $type;
      $_POST['id'] = $tickets->add($ticket);
    } else {
      $tickets->edit($ticket);
    }
    
    return $html->h2->successedit;
  }
  
}//class
?>