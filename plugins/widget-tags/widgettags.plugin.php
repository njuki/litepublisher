<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class twidgettags extends tplugin {
  public $widget;
  public $id;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
public function load() {}
  
  public function install() {
    litepublisher::$classes->gettemplatevar = $this->getvar;
  }
  
  public function uninstall() {
    litepublisher::$classes->unsubscribeclass($this);
  }
  
  public function getvar($name) {
    switch ($name) {
      case 'categories':
      $widget = tcategorieswidget::instance();
      break;
      
      case 'tags':
      $widget = ttagswidget::instance();
      break;
      
      case 'archives':
      $widget = tarchiveswidget::instance();
      break;
      
      case 'links':
      $widget = tlinkswidget::instance();
      break;
      
      case 'posts':
      $widget = tpostswidget::instance();
      break;
      
      case 'meta':
      $widget = tmetawidget::instance();
      break;
      
      default:
      return;
    }
    
    $result = new self();
    $result->widget = $widget;
    $widgets = twidgets::instance();
    $result->id = $widgets->find($widget);
    return $result;
  }
  
  public function __get($name) {
    switch ($name) {
      case 'title':
      return $this->widget->gettitle($this->id);
      
      case 'items':
      case 'content':
      return $this->widget->getcontent($this->id, twidgets::instance()->currentsidebar);
    }
    
    return parent::__get($name);
  }
  
}//class
?>