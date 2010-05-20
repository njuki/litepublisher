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
  
  protected function getcontentpage($page) {
    $result = '';
    if ($this->poll > 0) {
      $polls = tpolls::instance();
      $result .= $polls->gethtml($this->poll, true);
dumpstr($result);
    }
    
    $result .= parent::getcontentpage($page);
    return $result;
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
var_dump($result);
    $this->filtered = $result;
  }
  
  public function getticketcontent() {
    self::checklang();
    $lang = tlocal::instance('ticket');
    $args = targs::instance();
    foreach (array('type', 'state', 'prio') as $prop) {
      $value = $this->$prop;
      $args->$prop = $lang->$value;
    }
    $args->reproduced = $this->reproduced ? $lang->yesword : $lang->noword;
    $args->assignto = $this->assigntoname;
    $args->author = $this->authorlink;
    
    ttheme::$vars['ticket'] = $this;
    $theme = ttheme::instance();
    $tml = file_get_contents($this->resource . 'ticket.tml');
return $theme->parsearg($tml, $args);
  }
  
  protected function getauthorname() {
    return $this->getusername($this->author, false);
  }
  
  protected function getauthorlink() {
    return $this->getusername($this->author, true);
  }
  
  protected function getassigntoname() {
    return $this->getusername($this->assignto, true);
  }
  
  private function getusername($id, $link) {
    if ($id == 0) return '';
    if ($id == 1) {
      $profile = tprofile::instance();
      return $profile->nick;
    } else {
      $users = tusers::instance();
      $account = $users->getitem($id);
      if (!$link || ($account['url'] == '')) return $account['name'];
      return sprintf('<a href="%s/users.htm%sid=%s">%s</a>',litepublisher::$options->url, litepublisher::$options->q, $id, $account['name']);
    }
  }
  
  public function closepoll() {
    $polls = tpolls::instance();
    $polls->db->setvalue($this->poll, 'status', 'closed');
  }
  
  public static function getresource() {
    return dirname(__file__) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;
  }
  
  public static function checklang() {
    if (!isset(tlocal::$data['ticket'])) {
      tlocal::loadini(self::getresource() . litepublisher::$options->language . '.ini');
      tfiler::serialize(litepublisher::$paths->languages . litepublisher::$options->language . '.php', tlocal::$data);
      tfiler::ini2js(tlocal::$data , litepublisher::$paths->files . litepublisher::$options->language . '.js');
    }
  }
  
  public function getschemalink() {
    return 'ticket';
  }
  
}//class
?>