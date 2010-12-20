<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tticket extends tchildpost {
  
  public static function instance($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  protected function create() {
    parent::create();
    $this->childtable = 'tickets';
    $this->data['childdata'] = &$this->childdata;
    $this->childdata = array(
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
  
  public function fixdata() {
    $this->childdata['reproduced'] = $this->childdata['reproduced'] == '1';
  }

public function gethead() {
$result = parent::gethead();
    if ($this->poll > 0) {
      $polls = tpolls::instance();
      $result .= $polls->gethead();
}
return  $result;
}
  
  protected function getclosed() {
    return strtotime($this->childdata['closed']);
  }
  
  protected function setclosed($value) {
    $this->childdata['closed'] = is_int($value) ? sqldate($value) : $value;
  }
  
  protected function getcontentpage($page) {
    $result = '';
    if ($this->poll > 0) {
      $polls = tpolls::instance();
      $result .= $polls->gethtml($this->poll, true);
    }
    
    $result .= parent::getcontentpage($page);
    return $result;
  }
  
  public function updatefiltered() {
    $result = $this->getticketcontent();
    $filter = tcontentfilter::instance();
    $filter->filterpost($this,$this->rawcontent);
    $result .= $this->filtered;
    if (!empty($this->childdata['code'])) {
      self::checklang();
      $lang = tlocal::instance('ticket');
      $result .= sprintf('<h2>%s</h2>', $lang->code);
      $result .= highlight_string($this->code, true);
    }
    $this->filtered = $result;
  }
  
  public function getticketcontent() {
    self::checklang();
    $lang = tlocal::instance('childdata');
    $args = targs::instance();
    foreach (array('type', 'state', 'prio') as $prop) {
      $value = $this->$prop;
      $args->$prop = $lang->$value;
    }
    $args->reproduced = $this->reproduced ? $lang->yesword : $lang->noword;
    $args->assignto = $this->assigntoname;
    $args->author = $this->authorlink;
    
    ttheme::$vars['ticket'] = $this;
    $theme = $this->theme;
    $tml = file_get_contents($this->resource . 'ticket.tml');
    return $theme->parsearg($tml, $args);
  }
  
  protected function getassigntoname() {
    return $this->getusername($this->assignto, true);
  }
  
  public function closepoll() {
    $polls = tpolls::instance();
    $polls->db->setvalue($this->poll, 'status', 'closed');
  }
  
  public static function getresource() {
    return dirname(__file__) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;
  }
  
  public static function checklang() {
    tlocal::loadsection('', 'ticket', self::getresource());
  }
  
  public function getschemalink() {
    return 'ticket';
  }
  
}//class
?>