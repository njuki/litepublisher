<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class twidget extends tdata {
public $template;
protected $adminclass;

protected function create() {
parent::create();
$this->basename = 'widget';
$this->cache = 'cache';
$this->template = 'widget';
$this->adminclass = 'tadminwidget';
}

protected function getadmin() {
if (($this->adminclass != '') && class_exists($this->adminclass)) {
$admin = getinstance($this->adminclass);
$admin->widget = $this;
return $admin;
}
$this->error(sprintf('The "%s" admin class not found', $this->adminclass));
}

  public function getwidget($id, $sitebar) {
try {
$title = $this->gettitle($id);
      $content = $this->getcontent($id, $sitebar);
    } catch (Exception $e) {
litepublisher::$options->handexception($e);
return '';
}
    
    $theme = ttheme::instance();
return $theme->getwidget($title, $content, $this->template, $sitebar);
  }

public function gettitle($id) {
if (isset($this->data['title'])) return $this->data['title'];
return '';
}

protected function settitle($value) {
$widgets = gtwidgets::instance();
if ($id = $widgets->find($this)) {
if ($widgets->items[$id]['title'] != $value) {
$widgets->items[$id]['title'] = $value;
$widgets->save();
}
}
if (isset($this->data['title']) && ($value != $this->data['title'])) {
$this->data['title'] = $value;
$this->save();
}
}
  
public function getcontent($id, $sitebar) {
return '';
}

public static function getcachefilename($id) {
$theme = ttheme::instance();
return litepublisher::$paths->cache . sprintf('widget.%s.%d.php', $theme->name, $id);
}

public function expired($id) {
switch ($this->cache) {
case 'cache':
case true:
$cache = twidgetscache::instance();
$cache->expired($id);
break;

case 'include':
$sitebar = self::getsitebar($id);
$filename = self::getcachefilename($id, $sitebar);
file_put_contents($filename, $this->getwidget($id, $sitebar);
break;
}
}

public static function getsitebar($id) {
$widgets = twidgets::instance();
foreach ($widgets->sitebars as $i=> $sitebar) {
foreach ($sitebar as $item) {
if ($id == $item['id']) return $i;
}
}
return 0;
}

public function expire() {
$widgets = twidgets::instance();
foreach ($widgets->items as $id => $item) {
if ($this instanceof $item['class']) $this->expired($id);
}
}
  
}//class

class twidgets extends titems {
public $sitebars;
public classes;$
  public $currentsitebar;
  public $idwidget;

  public static function instance($id = null) {
    return getinstance(__class__);
  }
  
  protected function create() {
$this->dbversion = false;
    parent::create();
    $this->addevents('onwidget', 'onadminlogged', 'onadminpanel', 'ongetwidgets', 'onsitebar');
$this->basename = 'widgets';
    $this->currentsitebar = 0;
$this->addmap('sitebars', array());
$this->addmap('classes', array());
  }

  public function add(twidget $widget) {
return $this->additem( array(
'class' => get_class($widget),
'cache' => $widget->cache,
'title' => $widget->gettitle(0),
'template' => $widget->template
));
}

  public function addext(twidget $widget, $title, $template) {
return $this->additem( array(
'class' => get_class($widget),
'cache' => $widget->cache,
'title' => $title,
'template' => $template
));
}

public function addclass(twidget $widget, $class) {
$this->lock();
$id = $this->add($widget);
if (!isset($this->classes[$class])) $this->classes[$class] = array();
$this->classes[$class][] = array(
'id' => $id,
'order' => 0,
'sitebar' => 0,
'ajax' => false
);
$this->unlock();
return $id;
}

public function delete($id) {
if (!isset($this->items[$id])) return;

for ($i = count($this->sitebars) - 1; $i >= 0; $i--) {
foreach ($this->sitebars[$i] as $j => $item) {
if ($id == $item['id']) array_delete($this->sitebars[$i], $j);
}
}

foreach ($this->classes as $class => $items) {
foreach ($items as $i => $item) {
if ($id == $item['id']) array_delete($this->classes[$class], $i);
}
}

unset($this->items[$id]);
$this->deleted($id
}

public function deleteclass($class) {
$this->unsubscribeclassname($class);
$deleted = array();
foreach ($this->items as $id => $item) {
if($class == $item['class']) {
unset($this->items[$id]);
$deleted[] = $id;
}
}

if (count($deleted) > 0) {
foreach ($this->sitebars as $i => $sitebar) {
foreach ($sitebar as $j => $item) {
if (in_array($item['id'], $deleted)) array_delete($this->sitebars[$i], $j);
}
}
}

if (isset($this->classes[$class])) unset($this->classes[$class]);
$this->save();
}

public function getwidget($id) {
if (!isset($this->items[$id])) return $this->error("The requested $id widget not found");
$class = $this->items[$id]['class'];
if (!class_exists($class)) {
$this->delete($id);
return $this->error("The $class class not found");
}
return getinstance($class);
}

  public function getsitebar($context) {
$sitebar = $this->currentsitebar;
$items = $this->getwidgets($context, $sitebar);
$theme = ttheme::instance();
      if ($theme->sitebarscount == $sitebar + 1) {
        for ($i = $sitebar + 1; $i < count($this->items); $i++) {
$items = $this->joinitems($items, $this->getwidgets($context, $i));
}
}

if ($context instanceof itemplate2) $context->getwidgets($items, $sitebar);
    if (litepublisher::$options->admincookie) $this->callevent('onadminlogged', array(&$items, $sitebar));
    if (litepublisher::$urlmap->adminpanel) $this->callevent('onadminpanel', array(&$items, $sitebar));
    $this->callevent('ongetwidgets', array(&$items, $sitebar));
      $result = $this->getsitebarcontent($items, $sitebar);
    $this->callevent('onsitebar', array(&$result, $this->currentsitebar++));
    return $result;
  }

private function getwidgets($context, $sitebar) {
$items = $this->sitebars[$sitebar];
$subitems =  $this->getsubitems($context, $sitebar);
$result = $this->joinitems($items, $subitems);
}

private function getsubitems($context, $sitebar) {
$result = array();
foreach ($this->classes as $class => $items) {
if ($context instanceof $class) {
foreach ($items as  $item) {
if ($sitebar == $item['sitebar']) $result[] = $item;
}
}
}
return $result);
}

private function joinitems(array $items, array $subitems) {
if (count($subitems) == 0) return $items;
if (count($items) == 0) return $subitems;
//delete copies
for ($i = count($items) -1; $i >= 0; $i--) {
$id = $items[$i]['id'];
foreach ($subitems as $subitem) {
if ($id == $subitem['id']) array_delete($items, $i);
}
}

//join
foreach ($subitems as $item) {
$count = count($items);
$order = $item['order'];
    if (($order < 0) || ($order >= $count)) $order = $count - 1;
array_insert($items, $item, $order);
}
}
return $items;
}

private function getsitebarcontent(array $items, $sitebar) {
$result = '';
$cache = twidgetscache::instance();
foreach ($items as $item) {
$id = $item['id'];
if ($item['ajax']) {
$content = $this->getajax($id, $sitebar);
} else {
switch ($this->items[$id]['cache']) {
case 'cache':
case true:
$content = $cache->getcontent($id, $sitebar);
break;

case 'include':
$content = $this->include($id, $sitebar);
break;

case 'nocache':
case false:
$widget = getinstance($this->items[$id]['class']);
$content = $widget->getwidget($id, $sitebar);
break;

case 'code':
$content = $this->getcode($id, $sitebar);
break;
}
}
    $this->callevent('onwidget', array($id, &$content));
$result .= $content;
}
return $result;
}

public function getajax($id, $sitebar) {
$title = sprintf('<a onclick="widgets.load(this, %d, %d)">%s</a>', $id, $sitebar, $this->items[$id]['title']);
      $content = "<!--widgetcontent-$id-->";
    $theme = ttheme::instance();
return $theme->getwidget($title, $content, $thisitems[$id]['template'], $sitebar);
}

private function include($id, $sitebar) {
$filename = twidget::getcachefilename($id, $sitebar);
if (!file_exists($filename)) {
$widget = $this->getwidget($id);
$content = $widget->getwidget($id, $sitebar);
file_put_contents($filename, $content);
@chmod($filename, 0666);
}
return "\n<?php @include('$filename'); ?>\n";
}

private function getcode($id, $sitebar) {
$class = $this->items[$id]['class'];
return "\n<?php
    \$widget = $class::instance();
    echo \$widget->getwidget($id, $sitebar);
      ?>\n";
}


public function find($class) {
foreach ($this->items as $id => $item) {
if ($class == $item['class']) return $id;
}
return false;
}

  public function xmlrpcgetwidget($id, $sitebar) {
if (!isset($this->items[$id])) return $this->error("Widget $id not found");
switch ($this->items[$id]['cache']
case 'cache':
case true:
$cache = twidgetscache::instance();
return $cache->getcontent($id, $sitebar);

case 'nocache':
case false:
case 'code':
$widget = getinstance($this->items[$id]['class']);
return  $widget->getwidget($id, $sitebar);

case 'include':
$filename = twidget::getcachefilename($id, $sitebar);
if (file_exists($filename)) {
return file_get_contents($filename);
} else {
$widget = $this->getwidget($id);
$content = $widget->getwidget($id, $sitebar);
file_put_contents($filename, $content);
@chmod($filename, 0666);
return $content;
}
}
}


public function getpos($id) {
return tsitebars::getpos($this->sitebars, $id);
}

public function setpos($id, $sitebar, $order) {
$this->sitebars = tsitebars::setpos($this->sitebars, $id, $sitebar, $order);
$this->save();
}

public function &finditem($id) {
foreach ($this->classes as $class => $items) {
foreach ($items as $item) {
if ($id == $item['id']) return $this->classes[$class][$i];
}
}
return false;
}

}//class

class twidgetscache extends titems {
  private $modified;

  public static function instance($id = null) {
    return getinstance(__class__);
  }
  
  protected function create() {
$this->dbversion = false;
    parent::create();
    $this->modified = false;
}

  public function getbasename() {
$theme = ttheme::instance();
return 'widgetscache.' . $theme->name;
}

  public function savemodified() {
    if ($this->modified) parent::save();
    $this->modified = false;
  }
  
  public function save() {
    $this->modified = true;
  }
  
public function getcontent($id, $sitebar) {
if (isset($this->items[$id][$sitebar])) return $this->items[$id][$sitebar];
return $this->setcontent($id, $sitebar);
}

public function setcontent($id, $sitebar) {
$widgets = twidgets::instance();
$widget = $widgets->getwidget($id);
$result = $widget->getcontent($id, $sitebar);
$this->items[[$id][$sitebar] = $result;
$this->save();
return $result;
}

public function expired($id) {
if (isset($this->items[$id])) {
unset($this->items[$id]);
$this->save();
}
}

}//class

class tsitebars extends tdata {
public $items;

  public static function instance($id = null) {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
$widgets = twidgets::instance();
$this->items = &$widgets->sitebars;
  }

public function load() {}

public function save() {
twidgets::instance()->save();
}

public function add($id) {
$this->insert($id, false, 0, -1);
}

public function insert($id, $ajax, $index, $order) {
if (!isset($this->items[$index])) return $this->error("Unknown sitebar $index");
$item = array('id' => $id, 'ajax' => $ajax);
    if (($order < 0) || ($order > count($this->items[$index]))) {
$this->items[$index][] = $item;
} else {
array_insert($this->sitebars[$index], $item, $order);
}
$this->save();
}

public function delete($id, $index) {
if ($i = $this->indexof($id, $index)) {
array_delete($this->items[$index], $i);
$this->save();
return $i;
}
}}
return false;
}

public function indexof($id, $index) {
foreach ($this->items[$index] as $i => $item) {
if ($id == $item['id']) return $i;
}
return false;
}

public function move($id, $index, $neworder) {
if ($old = $this->indexof($id, $index)) {
if ($old != $newindex) {
array_move($this->items[$index], $old, $newindex);
$this->save();
}
}
}

public static function getpos(array $sitebars, $id) {
foreach ($sitebars as $i => $sitebar) {
foreach ($sitebar as $j => $item) {
if ($id == $item['id']) return array($i, $j);
}
}
return false;
}

public static function setpos(array $items, $id, $newsitebar, $neworder) {
if ($pos = self::getpos($items, $id)) {
list($oldsitebar, $oldorder) = $pos;
if (($oldsitebar != $newsitebar) || ($oldorder != $neworder)){
$item = $items[$oldsitebars][$oldorder];
array_delete($items[$oldsitebar], $oldorder);
array_insert($items[$newsitebar], $neworder, $item);
}
}
return $items;
}

}//class

?>