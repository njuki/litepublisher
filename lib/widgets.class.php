<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class twidgets extends titems {
public $current;

  public static function instance() {
    return getinstance(__class__);
  }

protected function create() {
parent::create();
$this->basename = 'widgets';
    $this->addevents('ongetcontent');
}  

  public function add($class, $echotype, $sitebar, $order) {
return $this->addext($class, $echotype, '', '', $sitebar, $order);
}

  public function addext($class, $echotype, $template, $title, $sitebar, $order) {
$sitebars = tsitebars::instance();
    if ($sitebar >= $sitebars->count) return $this->error("sitebar index $sitebar cant more than sitebars count in template");
    if (!in_array($echotype, array('echo', 'include', 'nocache'))) $echotype = 'echo';
    $this->items[++$this->autoid] = array(
    'class' => $class,
    'echotype' => $echotype,
    'template' => $template,
    'title' => $title,
    'sitebar' => $sitebar
    );
    
$sitebars->add($this->autoid, $sitebar, $order);
    $this->save();
    $this->added($this->autoid);
    return $this->autoid;
  }

  public function deleteclass($class) {
    $this->lock();
    foreach ($this->items as $id => $item) {
      if ($item['class'] == $class) $this->delete($id);
    }
    $this->unlock();
  }
  
  public function delete($id) {
    if (!isset($this->items[$id])) return false;
$sitebars = tsitebars::instance();
$sitebars->deletewidget($id);
            unset($this->items[$id]);
      $this->save();
      $this->deleted($id);
$urlmap = turlmap::instance();
$urlmap->clearcache();
  }

  public function find($class) {
    foreach ($this->items as $id => $item) {
      if ($item['class'] == $class) return $id;
    }
    return false;
  }
  
  public static function  expired($instance) {
    $self = self::instance();
    $self->setexpired(get_class($instance));
  }
  
  public function setexpired($class) {
    foreach ($this->items as $id => $item) {
      if ($item['class'] == $class) $this->itemxpired($id);
    }
  }

public function itemexpired($id) {
$urlmap = turlmap::instance();
if ($this->items[$id]['echotype'] == 'echo') {
$urlmap->clearcache();
} else {
$urlmap->expiredname('widget', $id);
}
}  

  public function getcontent($id) {
$this->current = $id;
    $class = $this->items[$id]['class'];

    switch ( $echotype = $this->items[$id]['echotype']) {
      case 'echo':
        $result = $this->dogetcontent($class, $id);
      break;
      
      case 'include':
    $file = $paths['cache']. "widget$id.php";
      if (!@file_exists($file)) {
        $result = $this->dogetcontent($class, $id);
        file_put_contents($file, $result);
        @chmod($file, 0666);
      }
      $result = "\n<?php @include(\$GLOBALS['paths']['cache']. 'widget$id.php'); ?>\n";
      break;
      
      case 'nocache':
$sitebars = tsitebars::instance();
      $result = "\n<?php 
\$widget = getinstance('$class');
echo \$widget->getwidget($id, $sitebars->current);
?>\n";
      break;
    }
    
    $s = $this->ongetcontent($result);
    if ($s != '') $result = $s;
    return $result;
  }
  
  private function dogetcontent($class, $id) {
    global $options;
    if (!@class_exists($class)) {
      $this->delete($id);
      return '';
    }
    $result = '';
$sitebars = tsitebars::instance();
    $widget = GetInstance($class);
    try {
      if (empty($this->items[$id]['template'])) {
        $result =   $widget->getwidget($id, $sitebars->current);
      }else {
$theme= ttheme::instance();
$result = $theme->getwidget($this->widgets[$id]['title'], $widget->getwidgetcontent($id, $sitebars->current), 
$this->widgets[$id]['template'], $sitebars->current);
      }
    } catch (Exception $e) {
      $options->handexception($e);
    }
    return $result;
  }

  public function changesitebar($id, $sitebar) {
    if (!isset($this->items[$id])) return false;
    $oldsitebar = $this->items[$id]['sitebar'];
    if ($sitebar == $oldsitebar) return;
$sitebars = tsitebars::instance();
      $i = array_search($id, $sitebars->items[$oldsitebar]);
      array_splice($sitebars->items[$oldsitebar],  $i, 1);
      $sitebars->items[$sitebar][] = $id;
$sitebars->save();
      $this->items[$id]['sitebar'] = $sitebar;
      $this->save();
  }
  
  public function changeorder($id, $order) {
    if (!isset($this->widgets[$id])) return false;
    $sitebar = $this->widgets[$id]['sitebar'];
$sitebars = tsitebars::instance();
    if (($order < 0) || ($order > $sitebars->getcount($sitebar))) $order = $sitebars->getcount($sitebar);
    $oldorder = array_search($id, $sitebars->items[$sitebar]);
    if ($oldorder == $order) return
      array_splice($sitebars->items[$sitebar], $oldorder, 1);
      array_splice($sitebars->items[$sitebar], $order, 0, $id);
      $sitebars->save();
    }
  
  public function hasclass($class) {
    foreach ($this->items as $id => $item) {
      if ($item['class'] == $class) return true;
    }
    return false;
  }
  
 
}//class
?>