<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tview extends tdata {
  public $name;
  public $sitebars;
  
  public static function instance($name = '') {
    if ($name == '') $name = 'default';
    $owner = self::getowner();
    return $owner->getview($name);
  }
  
  public static function getowner() {
    return tviews::instance();
  }
  
  public static function getsitebars($context) {
    $view = self::getview($context);
    return view->sitebars;
  }
  
  public static function getview($context) {
    $name = $context->view;
    if ($name == '') $name = 'default';
    $owner = self::getowner();
    if (!isset($owner->items[$name])) {
      $name = 'default';
      $context->view = $name;
    }
    return $owner->getview($name);
  }
  
  public function __construct($name) {
    parent::__construct();
    $this->name = $name;
    $owner = self::getowner();
    $this->sitebars = &$owner->items[$name]['sitebars'];
    $this->data= &$owner->items[$name];
  }
public function load() {}
  public function save() {
    self::getowner()->save();
  }
  
}//class

class tviews extends titems {
  private $instances;
  public $classes;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = false;
    parent::create();
    $this->basename = 'views';
    $this->instances = array();
    $this->addmap('classes', array());
  }
  
  public function add($name) {
    if (!isset($this->items[[$name]){
      $this->items[$name] = array(
      'theme' => 'default',
      'tml' => 'index',
      'defaultsitebar' => true,
      'ajax' => false,
      'sitebars' => array(array(), array(), array())
      );
      $this->save();
    }
    return $this->getview($name);
  }
  
  public function delete(name) {
    if (!isset($this->items[name])) return false;
    foreach ($this->classes as $name => $iditem) {
      if ($id == $iditem) $this->classes = 0;
    }
    unset($this->items[$name]);
    unset($this->instances[$name]);
    $this->save();
    $this->deleted($id);
    return true;
  }
  
  public function getview($id) {
    if (isset($this->instances[$name])) return $this->instances[$name];
    if (!isset($this->items[$name])) $this->error("The '$name' view not found");
    $result = litepublisher::$classes->newinstance('tview');
    $this->instances[$name] = $result;
    return $result;
  }
  
  public function widgetdeleted($id) {
    foreach ($this->items as $name  => $view) {
      for ($i = count($view['sitebars']) - 1; $i >= 0; $i--) {
        foreach ($view['sitebar'][$i] as $j => $item) {
          if ($id == $item['id']) array_delete($this->items[$name]['sitebars'][$i], $j);
        }
      }
    }
    $this->save();
  }
  
}//class

?>