<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tview extends titem {
public $sitebars;
private $themeinstance;

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
'customtheme' => array(),
      'customsitebar' => false,
      'ajax' => false,
'sitebars' => array()
);
$this->sitebars = &$this->data['sitebars'];
  }

public function load() {
$views = tviews::instance();
if ($views->itemexists($this->id)) {
$this->data = &$views->items[$this->id];
$this->sitebars = &$this->data['sitebars'];
return true;
}
return false;
}

public function save() {
return tviews::instance()->save();
}

public function gettheme() {
if (isset($this->themeinstance) && ($this->themename == $this->themeinstance->name)) return $this->themeinstance;
if (!ttheme::exists($this->themename)) {
$this->themename = 'default';
$this->data['customtheme'] = array();
$this->save();
}
$this->themeinstance = ttheme::instance($this->themename);
$this->themeinstance->templates['custom'] = $this->data['customtheme'];
return $this->themeinstance;
}

public function setcustomsitebar($value) {
if ($value != $this->customsitebar) {
if ($this->id == 1) return false;
if ($value) {
$default = tview::instance(1);
$this->sitebars = $default->sitebars;
} else {
$this->sitebars = array();
}
$this->data['customsitebar'] = $value;
$this->save();
}
}

}//class

class tviews extends titems {
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
  
public function load() {
return tstorage::load($this);
}

public function save() {
return tstorage::save($this);
}

  public function add($name) {
$this->lock();
$id = ++$this->autoid;
$view = litepublisher::$classes->newitem(tview::getitemname(), 'tview', $id);
$view->id = $id;
$view->name = $name;
$this->items[$id] = &$view->data;
      $this->unlock();
return $id;
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