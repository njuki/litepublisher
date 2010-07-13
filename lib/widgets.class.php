<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class twidget extends tdata {
public $pageclass;
public $template;

protected function create() {
parent::create();
$this->basename = 'widget';
$this->cache = 'cache';
$this->pageclass = '';
$this->template = 'widget';
}

  public function getwidget($id, $sitebar) {
try {
$title = $this->gettitle();
      $content = $this->getcontent($id, $sitebar);
    } catch (Exception $e) {
litepublisher::$options->handexception($e);
return '';
}
    
    $theme = ttheme::instance();
return $theme->getwidget($title, $content, $this->template, $sitebar);
  }

public function gettitle($id) {
return '';
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
$sitebar = $this->getsitebar($id);
$filename = self::getcachefilename($id, $sitebar);
file_put_contents($filename, $this->getwidget($id, $sitebar);
break;
}
}

public function getsitebar($id) {
$widgets = twidgets::instance();
foreach ($widgets->sitebars as $i=> $sitebar) {
foreach ($sitebar as $item) {
if ($id == $item['id']) return $i;
}
}
return 0;
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
    $this->addevents('onwidget', 'oncurrentsitebar');
$this->basename = 'widgets';
    $this->currentsitebar = 0;
$this->addmap('sitebars', array());
$this->addmap('classes', array());
  }

  public function add(twidget $widget) {
return $this->additem( array(
'class' => get_class($widget),
'pageclass' => $widget->pageclass,
'cache' => $widget->cache,
'title' => $widget->title,
'template' => $widget->template
));
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
$items = $this->sitebars[$this->currentsitebar];
$subitems =  $this->getsubitems($context, $this->currentsitebar))
$items = $this->joinitems($items, $subitems);
      $result = $this->getsitebarcontent($items, $this->currentsitebar);
    $this->callevent('oncurrentsitebar', array(&$result, $this->currentsitebar++));
    return $result;
  }

private function getsubitems($context, $sitebar) {
$result = array();
foreach ($this->classes as $class => $items) {
if ($context instanceof $class) {
foreach ($items as $ $item) {
if ($sitebar == $item['sitebar']) $result[] = $item;
}
}
}
return $result);
}

private function joinitems(array $items, array $subitems) {
if (count($subitems) == 0) return $items;
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
switch ($this->items[$item['id']]['cache']) {
case 'cache':
case true:
$content = $cache->getcontent($id, $sitebar);
break;

case 'include':
$content = $this->include($id, $sitebar);
break;

case 'nocache':
case false:
$content = $this->nocache($id, $sitebar);
break;
}
}
    $this->callevent('onwidget', array($id, &$content));
$result .= $content;
}
return $result;
}

public function getajax($id, $sitebar) {
      $title = sprintf('<a onclick="widgets.load(this, %d)">%s</a>', $id, $this->items[$id]['title']);
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

private function nocache($id, $sitebar) {
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

public function ad($id) {
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

}//class

?>