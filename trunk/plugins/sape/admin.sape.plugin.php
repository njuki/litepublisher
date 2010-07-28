<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminsapeplugin extends tadminwidget {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->widget = tsapeplugin::instance();
  }
  
  public function getcontent(){
    $result = '';
    $widget = $this->widget;
    $args = targs::instance();
    if ($widget->id != 0) {
      $args->maxcount = $widget->counts[$widget->id];
      $result .= $this->optionsform($this->html->maxcountform($args));
    }
    
    $args->user = $widget->user;
    $args->force = $widget->force;
    $tml = file_get_contents(dirname(__file__) . DIRECTORY_SEPARATOR . 'sapeform.tml');
    $result .= $this->html->parsearg($tml, $args);
    return $result;
  }
  
  protected function doprocessform(twidget $widget)  {
    extract($_POST, EXTR_SKIP);
    if (isset($addwidget)) {
      $widget->add();
    } elseif (isset($sapeoptions)) {
      $widget->user = $user;
      $widget->force = isset($force);
    } else {
      $widget->counts[$widget->id] = (int) $maxcount;
    }
  }
  
}//class
?>