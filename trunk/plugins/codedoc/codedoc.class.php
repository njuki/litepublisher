<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tcodedoc extends tpost {
  public $doc;
  public $doctable;
  
  public static function instance($id = 0) {
    return parent::iteminstance('post', __class__, $id);
  }
  
  protected function create() {
    parent::create();
    $this->doctable = 'codedoc';
    $this->data['doc'] = &$this->doc;
    $this->doc = array(
    'id' => 0,
    'class'  => '',
  'parentclass' => '',
  'childs' => '',
  'interfaces' => '',
  'dependent' => '',
  'methods' => '',
  'properties' => '',
  'classevents' => '',
  'example' => ''
    );
  }
  
  public function __get($name) {
    if (array_key_exists($name, $this->doc)) return $this->doc[$name];
    return parent::__get($name);
  }
  
  public function __set($name, $value) {
    if (array_key_exists($name, $this->doc)) {
      $this->doc[$name] = $value;
      return true;
    }
    return parent::__set($name, $value);
  }
  
  public function __isset($name) {
    return array_key_exists($name, $this->doc) || parent::__isset($name);
  }
  
  protected function LoadFromDB() {
    if (!parent::LoadFromDB())  return false;
    if ($a = $this->getdb($this->doctable)->getitem($this->id)) {
      $this->doc = $a;
      return true;
    }
    return false;
  }
  
  protected function SaveToDB() {
    parent::SaveToDB();
    $this->doc['id'] = $this->id;
    $this->getdb($this->doctable)->updateassoc($this->doc);
  }
  
  public function addtodb() {
    $id = parent::addtodb();
    $this->doc['id'] = $id;
    $this->getdb($this->doctable)->add($this->doc);
    return $this->id;
  }
  
  protected function getcontentpage($page) {
    $result = '';
    if ($page == 1) $result .= $this->getdoccontent();
    $result .= parent::getcontentpage($page);
    if (($page == 1) && !empty($this->doc['code'])) {
      $code = str_replace(array('"', "'", '$'), array('&quot;', '&#39;', '&#36;'), htmlspecialchars($this->code));
      $result .= "\n<code><pre>\n$code\n</pre></code>\n";
    }
    return $result;
  }
  
  public function getdoccontent() {
    $this->checklang();
    $lang = tlocal::instance('doc');
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
    
    ttheme::$vars['doc'] = $this;
    $theme = ttheme::instance();
    $tml = file_get_contents($this->resource . 'doc.tml');
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
    if (!isset(tlocal::$data['doc'])) {
      tlocal::loadini($this->resource . litepublisher::$options->language . '.ini');
      tfiler::serialize(litepublisher::$paths->languages . litepublisher::$options->language . '.php', tlocal::$data);
      tfiler::ini2js(tlocal::$data , litepublisher::$paths->files . litepublisher::$options->language . '.js');
    }
  }
  
}//class
?>