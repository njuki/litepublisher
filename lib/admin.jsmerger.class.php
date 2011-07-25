<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminjsmerger extends tadminmenu {
  public static function instance($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function getcontent() {
    $js = tjsmerger::instance();
    $jsadmin = tadminjsmerger::instance();
    $html = $this->html;
    $lang = $this->lang;
    $args = targs::instance();
    
    $args->formtitle= $lang->edit;
    $args->jsfiles = implode("\n", $jsfiles->items);
    $args->adminjsfiles = implode("\n", $jsfiles->items);
    $result = $html->adminform('[editor=jsfiles] [editor=adminjsfiles]', $args));
    
  }
  
  public function processform() {
    $jsfiles = tjsfiles::instance();
    $jsfiles->setfromstring($_POST['jsfiles']);
    $adminjsfiles = tadminjsfiles::instance();
    $adminjsfiles->setfromstring($_POST['adminjsfiles']);
  }
  
}//class