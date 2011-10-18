<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tshortcode extends titems {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = false;
    parent::create();
    $this->basename = 'shortcodes';
  }
  
  public function getcodes() {
    $result = '';
    foreach ($this->items as $name => $value) {
      $result .= "$name = $value\n";
    }
    return $result;
    
  }
  
  public function setcodes($s) {
    $this->items  = tini2array::parsesection($s);
    $this->save();
  }
  
  public function filter(&$content) {
    foreach ($this->items as $code => $tml) {
      if (preg_match_all("/\[$code\=(.*?)\]/", $content, $m, PREG_SET_ORDER)) {
        foreach ($m as $item) {
          $value =         str_replace('$value', $item[1], $tml);
          $content = str_replace($item[0], $value, $content);
        }
      }
    }
  }
  
  public function install() {
    $filter = tcontentfilter::i();
    $filter->lock();
    $filter->beforefilter = $this->filter;
    $filter->oncomment = $this->filter;
    $filter->unlock();
  }
  
  public function uninstall() {
    $filter = tcontentfilter::i();
    $filter->unbind($this);
  }
  
}//class