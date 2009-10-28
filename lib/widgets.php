<?php

class twidgets extents TItems {
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
    'index' => $index
    );
    
$sitebars->add($this->autid, $sitebar, $order);
    $this->save();
    $this->addded($this->autoid);
    return $this->autoid;
  }


  public function deleteclass($class) {
    $this->lock();
    foreach ($this->items as $id => $item) {
      if ($item['class'] == $class) { $this->delete($id);
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

    switch ( $echotype = $this->widgets[$id]['echotype']) {
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
      $result = "\n<?php @include(\$GLOBALS['paths']['cache']. '$file'); ?>\n";
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
      if (empty($this->widgets[$id]['template'])) {
        $result =   $widget->getwidget($id, $sitebars->current);
      }else {
$result = $this->getcontenttemplate($this->widgets[$id]['title'], $widget->GetWidgetContent($id, $sitebars->current), 
$this->widgets[$id]['template'], $sitebars->current);
      }
    } catch (Exception $e) {
      $options->HandleException($e);
    }
    return $result;
  }

public function getcontenttemplate($title, $content, $template, $sitebar) {
$theme= ttheme::instance();
$tml = $theme->getwidget($template, $sitebar);
return sprintf($tml, $title, $content);
  }

  public function move($id, $index) {
    if (!isset($this->items[$id])) return false;
    $oldindex = $this->items[$id]['index'];
    if ($index != $oldindex) {
      $i = array_search($id, $this->sitebars[$oldindex]);
      array_splice($this->sitebars[$oldindex],  $i, 1);
      $this->sitebars[$index][] = $id;
      $this->widgets[$id]['index'] = $index;
      $this->save();
    }
  }
  
  public function MoveWidgetOrder($id, $order) {
    if (!isset($this->widgets[$id])) return false;
    $index = $this->widgets[$id]['index'];
    if (($order < 0) || ($order > count($this->sitebars[$index]))) $order = count($this->sitebars[$index]);
    $oldorder = array_search($id, $this->sitebars[$index]);
    if ($oldorder != $order) {
      array_splice($this->sitebars[$index], $oldorder, 1);
      array_splice($this->sitebars[$index], $order, 0, $id);
      $this->save();
    }
  }
  
  
  public function hasclass($class) {
    foreach ($this->items as $id => $item) {
      if ($item['class'] == $class) return true;
    }
    return false;
  }
  
 
}//class
?>