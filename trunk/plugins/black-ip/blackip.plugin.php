<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tblackip extends tplugin {
  public $ip;
  public $words;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->addmap('ip', array());
    $this->addmap('words', array());
    $this->data['ipstatus'] = 'hold';
    $this->data['wordstatus'] = 'hold';
  }
  
  public function filter($idpost, $idauthor, $content, $ip) {
    if (in_array($ip, $this->ip)) return $this->ipstatus;
    foreach ($this->words as $word) {
      if (false !== strpos($content, $word)) return $this->wordstatus;
    }
  }
  
}//class