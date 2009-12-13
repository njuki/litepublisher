<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class twidgets extends tsingleitems {
public $current;
public $curwidget;
public $curindex;
public $count;

public static function instance($id = 0) {
    return parent::instance(__class__, $id);
}

protected function create() {
parent::create();
$this->current = 0;
$theme = ttheme::instance();
$this->count = $theme->sitebarscount;
$this->dbversion = false;
    $this->addevents('ongetcontent');
}

public function getbasename() {
return 'sitebars' . DIRECTORY_SEPARATOR  . $this->id;
}

public function load() {
if (!isset($this->id)) return false;
if ($this->id > 0) return parent::load();

//значит id = 0 и сайтбары по умолчанию будем хранить в ttemplate
$template = ttemplate::instance();
$this->data = &$template->data['sitebars'];
}

public function save() {
if ($this->id > 0) return parent::save();

$template = ttemplate::instance();
$template->save();
}

public function getitem($id) {
for ($i = count($this->items) - 1; $i >= 0; $i--) {
if (isset($this->items[$i][$id])) return $this->items[$i][$id];
}
return false;
}

public function getcount($index) {
return count($this->items[$index]);
}

public function getcontent() {
global $paths;
$template = ttemplate::instance();
$file = $paths['cache'] . "$template->tml.sitebar.$this->id.$this->current.php";
if (file_exists($file)) {
$result = file_get_contents($file);
} else {
$result = $this->getcontent($this->current);
//если закончились сайтбары, то остатки объеденить
    if ($this->count == $this->current + 1) {
for ($i = $this->current + 1; $i < count($this->items); $i++) {
$result .= $this->getcontent($i);
}
}

file_put_contents($file, $result);
@chmod($file, 0666);
}
$template->onsitebar(&$result, $this->current++);
return $result;
}
	
  private function getsitebar($index) {
    $result = '';
$template = ttemplate::instance();
$i = 0;
    foreach ($this->items[$index] as $id => $item) {
$this->curwidget= $id;
$this->curindex= $i++;
$content = $this->getwidget($item);
$template->ongetwidget($id, &$content);
      $result .= $content;
    }
    return $result;
  }

private function getcachefilename($item) {
return "widget.$this->id.{$item['id']}.php";
}

private function getwidget($item) {
global $paths;
    switch ( $item['echotype']) {
      case 'echo':
        $result = $this->dogetwidget($item);
      break;
      
      case 'include':
    $filename = $this->getcachefilename($item);
$file = $paths['cache'] . $filename;
      if (!@file_exists($file)) {
        $result = $this->dogetwidget($item);
        file_put_contents($file, $result);
        @chmod($file, 0666);
      }
      $result = "\n<?php @include(\$GLOBALS['paths']['cache']. '$filename'); ?>\n";
      break;
      
      case 'nocache':
      $result = "\n<?php 
 \$widget = getinstance('{$item['class']}');
echo \$widget->getwidget({$item['id']}, $this->current);
?>\n";
      break;
    }
    
    return $result;
  }
  
  private function dogetwidget($item) {
    global $options;
    if (!@class_exists($item['class'])) {
      $this->deleteclass($item['class']);
      return '';
    }

    $result = '';
    $widget = GetInstance($item['class']);
    try {
      if (empty($item['template'])) {
        $result =   $widget->getwidget($item['id'], $this->current);
      }else {
$content = $widget->getwidgetcontent($item['id'], $this->current);
$theme= ttheme::instance();
$result = $theme->getwidget($item['title'], $content, $item['template'], $this->current);
      }
    } catch (Exception $e) {
      $options->handexception($e);
    }
    return $result;
  }
  public function add($class, $echotype, $sitebar, $order) {
return $this->addext($class, $echotype, '', '', $sitebar, $order);
}

  public function addext($class, $echotype, $template, $title, $sitebar, $order) {
    if ($sitebar >= $this->count) return $this->error("sitebar index $sitebar cant more than sitebars count in theme");
    if (($order < 0) || ($order > $this->getcount($sitebar))) $order = $this->getcount($sitebar);
    if (!preg_match('/echo|include|nocache/', $echotype)) $echotype = 'echo';
$id = ++$this->autoid;
$item =  array(
'id' => $id,
    'class' => $class,
    'echotype' => $echotype,
    'template' => $template,
    'title' => $title
    );

$this->insert($item, $sitebar, $order);
    $this->added($id);
    return $id;
  }

private function insert($item, $sitebar, $order) {
//вставить в массив с соблюдением порядка и ключей    
$new = array();
$i = 0;
foreach ($this->items[$sitebar] as $idWidget => $widget) {
if ($i++ == $order) $new[$item['id']] = $item;
$new[$idwidget] = $widget;
}
$this->items[$sitebar] = $new;
    $this->save();
}

  public function deleteclass($class) {
$deleted = false;
for ($i = count($this->items) - 1; $i >= 0; $i--) {
    foreach ($this->items[$i] as $id => $item) {
      if ($item['class'] == $class) {
unset($this->items[$i][$id]);
$this->deleted($id);
$deleted = true;
}
    }
}
if ($deleted) {
    $this->save();
$urlmap = turlmap::instance();
$urlmap->save();
}
  }

public function delete($idwidget) {
for ($i = count($this->items) - 1; $i >= 0; $i--) {
    foreach ($this->items[$i] as $id => $item) {
      if ($id == $idwidget)  {
unset($this->items[$i][$id]);
$this->save();
$this->deleted($id);
$urlmap = turlmap::instance();
$urlmap->clearcache();
return true;
}
    }
}
return false;
}

  public function findclass($class) {
for ($i = count($this->items) - 1; $i >= 0; $i--) {
    foreach ($this->items[$i] as $id => $item) {
      if ($class == $item['class'])  return $id;
}
}
    return false;
  }

public function findsitebar($id) {
for ($i = count($this->items) - 1; $i >= 0; $i--) {
if (isset($this->items[$i][$id])) return $i;
}
return false;
}

  public static function  expired($instance) {
    $self = self::instance(0);
    $self->setexpired(get_class($instance));
  }
  
  public function setexpired($class) {
global $paths;
for ($i = count($this->items) - 1; $i >= 0; $i--) {
    foreach ($this->items[$i] as $id => $item) {
      if ($class == $item['class'])  {
if ($item['echotype'] == 'echo') {
$urlmap = turlmap::instance();
$urlmap->clearcache();
return;
} else {
@unlink($paths['cache'] . $this->getcachefilename($item));
}
}
}
}
}


public function itemexpired($id) {
global $paths;
$item = $this->getitem($id);
if ($item['echotype'] == 'echo') {
$urlmap = turlmap::instance();
$urlmap->clearcache();
} else {
@unlink($paths['cache'] . $this->getcachefilename($item));
}
}  

  public function changesitebar($id, $sitebar) {
$oldsitebar = $this->findsitebar($id);
if ($oldsitebar == $sitebar) return;
$this->items[$sitebar][$id] = $this->items[$oldsitebar][$id];
unset($this->items[$oldsitebar][$id]);
      $this->save();
  }
  
  public function changeorder($id, $order) {
$sitebar = $this->findsitebar($id);
$i = 0;
foreach ($this->items[$sitebar] as $idwidget => $item) {
if ($id == $idwidget) break;
$i++;
}
    if ($i == $order) return;
$item = $this->items[$sitebar]['id'];
unset($this->items[$sitebar][$id]);
$this->insert($item, $sitebar, $order);
}

}//class
?>