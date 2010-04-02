<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tticket extends tpost {
  public $ticket;
  public $ticketstable;
  
  public static function instance($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  protected function create() {
    parent::create();
    $this->ticketstable = 'tickets';
    $this->data['ticket'] = &$this->ticket;
    $this->ticket = array(
    'id' => 0,
    'type' => 'bug',
    'state'  => 'opened',
    'prio' => 'major',
    'assignto' => 0,
    'closed' => '',
    'version'=> litepublisher::$options->version,
    'votes' => 0,
    'poll' => 0,
    'os'=> '*',
    'reproduced' => false,
    'code' => ''
    );
  }
  
  public function __get($name) {
    if ($name == 'id') return $this->data['id'];
    if (array_key_exists($name, $this->ticket)) return $this->ticket[$name];
    return parent::__get($name);
  }
  
  public function __set($name, $value) {
    if ($name == 'id') return $this->setid($value);
    if (array_key_exists($name, $this->ticket)) {
      $this->ticket[$name] = $value;
      return true;
    }
    return parent::__set($name, $value);
  }
  
  public function __isset($name) {
    return array_key_exists($name, $this->ticket) || parent::__isset($name);
  }
  
  protected function LoadFromDB() {
    if (!parent::LoadFromDB())  return false;
    if ($a = $this->getdb($this->ticketstable)->getitem($this->id)) {
      $this->ticket = $a;
      $this->ticket['reproduced'] = $a['reproduced'] == '1';
      return true;
    }
    return false;
  }
  
  protected function SaveToDB() {
    parent::SaveToDB();
    if ($this->ticket['closed'] == '') $this->ticket['closed'] = sqldate();
    $this->ticket['id'] = $this->id;
    $this->getdb($this->ticketstable)->updateassoc($this->ticket);
  }
  
  public function addtodb() {
    $id = parent::addtodb();
    $this->ticket['id'] = $id;
    $this->getdb($this->ticketstable)->add($this->ticket);
    return $this->id;
  }
  
  protected function getclosed() {
    return strtotime($this->ticket['closed']);
  }
  
  protected function setclosed($value) {
    $this->ticket['closed'] = sqldate($value);
  }
  
  public function updatefiltered() {
    $result = $this->getticketcontent();
    $filter = tcontentfilter::instance();
    $filter->filterpost($this,$this->rawcontent);
    $result .= $this->filtered;
    if (!empty($this->ticket['code'])) {
      $lang = tlocal::instance('ticket');
      $result .= sprintf('<h2>%s</h2>', $lang->code);
      $result .= highlight_string($this->code, true);
    }
    $this->filtered = $result;
  }
  
  public function getticketcontent() {
    $this->checklang();
    $lang = tlocal::instance('ticket');
    $args = targs::instance();
    foreach (array('type', 'state', 'prio') as $prop) {
      $value = $this->$prop;
      $args->$prop = $lang->$value;
    }
    $args->reproduced = $this->reproduced ? $lang->yesword : $lang->noword;
    if ($this->assignto <= 1) {
      $profile = tprofile::instance();
      $args->assignto = $profile->nick;
    } else {
      $users = tusers::instance();
      $account = $users->getitem($this->assignto);
      $args->assignto = $account['name'];
    }
    
    ttheme::$vars['ticket'] = $this;
    $theme = ttheme::instance();
    $tml = file_get_contents($this->resource . 'ticket.tml');
    $result = $theme->parsearg($tml, $args);
    
    if ($this->poll > 0) {
      $polls = tpolls::instance();
      $result .= $polls->gethtml($this->poll);
    }
    return $result;
  }
  
  public function closepoll() {
    $polls = tpolls::instance();
    $polls->db->setvalue($this->poll, 'status', 'closed');
  }
  
  protected function getresource() {
    return dirname(__file__) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;
  }
  
  public function checklang() {
    if (!isset(tlocal::$data['ticket'])) {
      tlocal::loadini($this->resource . litepublisher::$options->language . '.ini');
      tfiler::serialize(litepublisher::$paths->languages . litepublisher::$options->language . '.php', tlocal::$data);
      tfiler::ini2js(tlocal::$data , litepublisher::$paths->files . litepublisher::$options->language . '.js');
    }
  }
  
  public function getschemalink() {
    return 'ticket';
  }
  
}//class
?>