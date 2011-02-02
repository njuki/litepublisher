<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tdownloaditem extends tpost {
  
  public static function instance($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public static function getchildtable() {
    return 'downloaditem';
  }
  
  protected function create() {
    parent::create();
    $this->data['childdata'] = &$this->childdata;
    $this->childdata = array(
    'id' => 0,
    'type' => 'theme',
    'downloadurl'  => '',
    'authorurl'  => '',
'authorname' => '',
    'version'=> '1.00',
    'votes' => 0,
    'poll' => 0
    );
  }

public function getparenttag() {
return $this->type == 'theme' ? 
}

  public function settagnames($names) {
$names = trim($names);
if ($names == '') {
$this->tags = array();
return;
}
$parent = $this->getparenttag();
    $tags = ttags::instance();
    $items = array();
$list = explode(',', trim($names));
    foreach ($list as $title) {
      $title = tcontentfilter::escape($title);
      if ($title == '') continue;
      $items[] = $tags->add($parent, $title);
    }

    $this->tags=  $items;
  }

   public function gethead() {
    $result = parent::gethead();
$template = ttemplate::instance();
$result .= $template->getjavascript('/plugins/' . basename(dirname(__file__)) . '/downloaditem.js');
    if ($this->poll > 0) {
      $polls = tpolls::instance();
      $result .= $polls->gethead();
    }
    return  $result;
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
    $theme = $this->theme;
    $tml = file_get_contents($this->resource . 'ticket.tml');
    return $theme->parsearg($tml, $args);
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
  
}//class
?>