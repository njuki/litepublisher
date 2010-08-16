<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tsimplecontent  extends tdata implements itemplate {
  public $text;
  public $html;
  
  public function  httpheader() {
    return turlmap::htmlheader(false);
  }
  
public function request($arg) {}
public function gettitle() {}
public function getkeywords() {}
public function getdescription() {}
public function gethead() {}
  
  public function getcont() {
    $result = empty($this->text) ? $this->html : sprintf("<h2>%s</h2>\n", $this->text);
    $theme =ttheme::instance();
    return $theme->simple($result);
  }
  
  public static function html($content) {
    $class = __class__;
    $self = new $class();
    $self->html = $content;
    $template = ttemplate::instance();
    return $template->request($self);
  }
  
  public static function content($content) {
    $class = __class__;
    $self = new $class();
    $self->text = $content;
    $template = ttemplate::instance();
    return $template->request($self);
  }
  
}//class

?>