<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tview extends titem {
private $_theme;

  public static function instance($id = 1) {
    return parent::iteminstance(__class__, $id);
  }

  public static function getinstancename() {
    return 'view';
  }
  
  public static function getview($instance) {
$id = $instance->getview();
$views = tviews::instance();
if (!$views->itemexists($id)) {
$id = 1; //default, wich always exists
$instance->setview($id);
}
return self::instance($id);
  }

protected function create() {
parent::create();
$this->data = array(
'id' => 0,
'name' => 'default',
'themename' => 'default',
      'defaultsitebar' => true,
      'ajax' => false,
'sitebars' => array()
);
  }

public function load() {
$views = tviews::instance();
if ($views->itemexists($this->id)) {
$this->data = &$views->items[$this->id];
return true;
}
return false;
}

public function save() {
return tviews::instance()->save();
}

public function gettheme() {
if (isset($this_theme) && ($this->themename == $this_theme->name)) return $this->_theme;
if (!ttheme::exists($this->themename)) {
$this->themename = 'default';
$this->save();
}
$this->_theme = ttheme::instance($this->themename);
return $this->_theme;
}

}//class

class tviews extends titems {

  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = false;
    parent::create();
    $this->basename = 'views';
    $this->addmap('classes', array());
  }
  
  public function add($name) {
$this->lock();
$id = ++$this->autoid;
$view = litepublisher::$classes->newitem(tview::getitemname(), 'tview', $id);
$view->id = $id;
$view->name = $name;
$this->items[$id] = &$view->data;
      $this->unlock();
return $view;
  }
  
  public function delete($id) {
if ($id == 1) return $this->error('You cant delete default view');
return parent::delete($id);
  }
  
  public function widgetdeleted($idwidget) {
$deleted = false;
    foreach ($this->items as $id => $item) {
foreach ($item['sitebars'] as $i => sitebar) {
for ($j = count($sitebar) -1; $j >= 0; $j--) {
          if ($idwidget == $sitebar[$j]['id']) {
array_delete($this->items[$id]['sitebars'][$i], $j);
$deleted = true;
}
        }
      }
    }
if ($deleted) $this->save();
  }
  
}//class

?>