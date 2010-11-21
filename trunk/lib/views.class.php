<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tview extends titem {
  public $sidebars;
  private $themeinstance;
  
  public static function instance($id = 1) {
    return parent::iteminstance(__class__, $id);
  }
  
  public static function getinstancename() {
    return 'view';
  }
  
  public static function getview($instance) {
    $id = $instance->getidview();
    if (isset(self::$instances['view'][$id]))     return self::$instances['view'][$id];
    $views = tviews::instance();
    if (!$views->itemexists($id)) {
      $id = 1; //default, wich always exists
      $instance->setidview($id);
    }
    return self::instance($id);
  }
  
  protected function create() {
    parent::create();
    $this->data = array(
    'id' => 0,
    'name' => 'default',
    'themename' => 'default',
    'customsidebar' => false,
    'disableajax' => false,
    'custom' => array(),
    'sidebars' => array()
    );
    $this->sidebars = &$this->data['sidebars'];
    $this->themeinstance = null;
  }
  
  public function load() {
    $views = tviews::instance();
    if ($views->itemexists($this->id)) {
      $this->data = &$views->items[$this->id];
      $this->sidebars = &$this->data['sidebars'];
      return true;
    }
    return false;
  }
  
  public function save() {
    return tviews::instance()->save();
  }
  
  public function setthemename($name) {
    if ($name != $this->themename) {
      if (!theme::exists($name)) return $this->error(sprintf('Theme %s not exists', $name));
      $this->data['themename'] = $name;
      $this->themeinstance = ttheme::getinstance($name);
      $this->data['custom'] = $this->themeinstance->templates['custom'];
      $this->save();
    }
  }
  
  public function gettheme() {
    if (isset($this->themeinstance)) return $this->themeinstance;
    if (ttheme::exists($this->themename)) {
      $this->themeinstance = ttheme::getinstance($this->themename);
      $this->themeinstance->templates['custom'] = $this->data['custom'];
    } else {
      $this->setthemename('default');
    }
    return $this->themeinstance;
  }
  
  public function setcustomsidebar($value) {
    if ($value != $this->customsidebar) {
      if ($this->id == 1) return false;
      if ($value) {
        $default = tview::instance(1);
        $this->sidebars = $default->sidebars;
      } else {
        $this->sidebars = array();
      }
      $this->data['customsidebar'] = $value;
      $this->save();
    }
  }
  
}//class

class tviews extends titems_storage {
  public $defaults;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = false;
    parent::create();
    $this->basename = 'views';
    $this->addmap('defaults', array());
  }
  
  public function add($name) {
    $this->lock();
    $id = ++$this->autoid;
    $view = litepublisher::$classes->newitem(tview::getinstancename(), 'tview', $id);
    $view->id = $id;
    $view->name = $name;
    $this->items[$id] = &$view->data;
    $this->unlock();
    return $id;
  }
  
  public function delete($id) {
    if ($id == 1) return $this->error('You cant delete default view');
    foreach ($this->defaults as $name => $iddefault) {
      if ($id == $iddefault) $this->defaults[$name] = 1;
    }
    return parent::delete($id);
    
  }
  
  public function widgetdeleted($idwidget) {
    $deleted = false;
    foreach ($this->items as $id => $item) {
      foreach ($item['sidebars'] as $i => $sidebar) {
        for ($j = count($sidebar) -1; $j >= 0; $j--) {
          if ($idwidget == $sidebar[$j]['id']) {
            array_delete($this->items[$id]['sidebars'][$i], $j);
            $deleted = true;
          }
        }
      }
    }
    if ($deleted) $this->save();
  }
  
}//class


class tevents_itemplate extends tevents {
  
  protected function create() {
    parent::create();
    $this->data['idview'] = 1;
  }
  
public function gethead() {}
public function getkeywords() {}
public function getdescription() {}
  
  public function getidview() {
    return $this->data['idview'];
  }
  
  public function setidview($id) {
    if ($id != $this->idview) {
      $this->data['idview'] = $id;
      $this->save();
    }
  }
  
  public function getview() {
    return tview::getview($this);
  }
  
}//class


class titems_itemplate extends titems {
  
  protected function create() {
    parent::create();
    $this->data['idview'] = 1;
    $this->data['keywords'] = '';
    $this->data['description'] = '';
  }
  
public function gethead() {}
  public function getkeywords() {
    return $this->data['keywords'];
  }
  
  public function getdescription() {
    return $this->data['description'];
  }
  
  public function getidview() {
    return $this->data['idview'];
  }
  
  public function setidview($id) {
    if ($id != $this->data['idview']) {
      $this->data['idview'] = $id;
      $this->save();
    }
  }
  
  public function getview() {
    return tview::getview($this);
  }
  
}//class

?>