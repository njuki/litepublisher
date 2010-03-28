<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminform extends tevents implements itemplate {
  protected $formresult;
  protected $title;
  protected $section;
  
  public function gettitle() {
    return tlocal::$data[$this->section]['title'];
  }
  
public function gethead() {}
public function getkeywords() {}
public function getdescription() {}
  
  public function request($arg) {
    $this->cache = false;
    tlocal::loadlang('admin');
    $this->formresult = '';
    if (isset($_POST) && (count($_POST) > 0)) {
      if (get_magic_quotes_gpc()) {
        foreach ($_POST as $name => $value) {
          $_POST[$name] = stripslashes($_POST[$name]);
        }
      }
      $this->formresult = $this->processform();
    }
  }
  
  public function processform() {
    return '';
  }
  
  public function getcont() {
    $result = $this->formresult;
    $result .= $this->getcontent();
$theme = ttheme::instance();
    return sprintf($theme->content->simple, $result);
  }
  
  public function gethtml() {
    $result = THtmlResource ::instance();
    $result->section = $this->section;
    $lang = tlocal::instance($this->section);
    return $result;
  }
  
}//class
?>