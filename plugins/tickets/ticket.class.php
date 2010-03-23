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
    return parent::iteminstance('post', __class__, $id);
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
    if (array_key_exists($name, $this->ticket)) return $this->ticket[$name];
    return parent::__get($name);
  }
  
  public function __set($name, $value) {
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
    if ($page == 1) $result .= $this->getticketcontent();
    $result .= parent::getcontentpage($page);
    if (($page == 1) && !empty($this->ticket['code'])) {
      $code = str_replace(array('"', "'", '$'), array('&quot;', '&#39;', '&#36;'), htmlspecialchars($this->code));
      $result .= "\n<code><pre>\n$code\n</pre></code>\n";
    }
    return $result;
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
      $account = $users->getaccount($this->assignto);
      $args->assignto = $account['name'];
    }
    
    ttheme::$vars['ticket'] = $this;
    $theme = ttheme::instance();
    $tml = file_get_contents($this->resource . 'ticket.tml');
    $result = $theme->parsearg($tml, $args);
    if ($this->poll > 1) {
      $polls = tpolls::instance();
      $result .= $polls->gethtml($this->poll);
    }
    return $result;
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