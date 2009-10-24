<?php

class twidgets extents TItems {

  public static function instance() {
    return getinstance(__class__);
  }
  
public function load() {
$template = ttemplate::instance();
$this->data = &$template->data['widgets'];
    $this->AssignDataMap();
}

public function save() {
$template = ttemplate::instance();
$template->save();
}

public function lock() {
$template = ttemplate::instance();
$template->lock();
}

public function unlock() {
$template = ttemplate::instance();
$template->unlock();
}

  public function add($class, $echotype, $template, $title, $order = -1, $index = 0) {
$sitebars = tsitebars::instance();
    if ($index >= $sitebars->count) return $this->error("sitebar index $index cant more than sitebars count in template");
    if (!in_array($echotype, array('echo', 'include', 'nocache'))) $echotype = 'echo';
    $this->items[++$this->autoid] = array(
    'class' => $class,
    'echotype' => $echotype,
    'template' => $template,
    'title' => $title,
    'index' => $index
    );
    
$sitebars->add($this->autid, $index, $order);
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

  public function GetWidgetContent($id) {
    global $paths;
    $class = $this->items[$id]['class'];
    $this->curwidget = $id;
    $FileName = $paths['cache']. "widget$id.php";
    switch ( $echotype = $this->widgets[$id]['echotype']) {
      case 'echo':
      if (@file_exists($FileName)) {
        $result = file_get_contents($FileName);
      } else {
        $result = $this->DoGetWidgetContent($class, $id);
        file_put_contents($FileName, $result);
        @chmod($FileName, 0666);
      }
      break;
      
      case 'include':
      if (!@file_exists($FileName)) {
        $result = $this->DoGetWidgetContent($class, $id);
        file_put_contents($FileName, $result);
        @chmod($FileName, 0666);
      }
      $result = "\n<?php @include(\$GLOBALS['paths']['cache']. 'widget$id.php'); ?>\n";
      break;
      
      case 'nocache':
      $result = $this->DoGetWidgetContent($class, $id);
      break;
    }
    
    $s = $this->OnWidgetContent($result);
    if ($s != '') $result = $s;
    return $result;
  }
  
  private function DoGetWidgetContent($class, $id) {
    global $options;
    if (!@class_exists($class)) {
      $this->DeleteIdWidget($id);
      return '';
    }
    $result = '';
    $widget = GetInstance($class);
    try {
      if (empty($this->widgets[$id]['template'])) {
        $result =   $widget->getwidget($id);
      }else {
        $result = $this->GetBeforeWidget($this->widgets[$id]['template'], $this->widgets[$id]['title']);
        $result .=   $widget->GetWidgetContent($id);
        $result .= $this->GetAfterWidget();
      }
    } catch (Exception $e) {
      $options->HandleException($e);
    }
    return $result;
  }
  
  public function GetBeforeWidget($name, $title = '') {
    $i = $this->SitebarIndex + 1;
    $result = '';
    if (isset($this->theme["sitebar$i"][$name])) {
      $result = $this->theme["sitebar$i"][$name];
    } elseif (isset($this->theme["sitebar$i"]['before'])) {
      $result = $this->theme["sitebar$i"]['before'];
    } elseif (isset($this->theme['widget'])) {
      $theme = &$this->theme['widget'];
      if (isset($theme[$name])) {
        $result = $theme[$name];
      } elseif (isset($theme[$name . $i])) {
        $result = $theme[$name . $i];
      } elseif (isset($theme["before$i"])) {
        $result = $theme["before$i"];
      } elseif (isset($theme['before'])) {
        $result = $theme['before'];
      }
    }
    
    if (empty($title) && isset(TLocal::$data['default'][$name])) {
      $title=  TLocal::$data['default'][$name];
    }
    
    //eval("\$result =\"$result\n\";");
    $result = sprintf($result, $title);
    return str_replace("'", '"', $result);
  }
  
  public function GetAfterWidget() {
    $result = $this->AfterWidget($this->curwidget);
    $i = $this->SitebarIndex + 1;
    if (isset($this->theme["sitebar$i"]['after'])) {
      $result .= $this->theme["sitebar$i"]['after'];
    } elseif (isset($this->theme['widget']["after$i"])) {
      $result .= $this->theme['widget']["after$i"];
    } elseif (isset($this->theme['widget']['after'])) {
      $result .= $this->theme['widget']['after'];
    }
    
    return str_replace("'", '"', $result);
  }
  
  public function MoveWidget($id, $index) {
    if (!isset($this->widgets[$id])) return false;
    $oldindex = $this->widgets[$id]['index'];
    if ($index != $oldindex) {
      $i = array_search($id, $this->sitebars[$oldindex]);
      array_splice($this->sitebars[$oldindex],  $i, 1);
      $this->sitebars[$index][] = $id;
      $this->widgets[$id]['index'] = $index;
      $this->Save();
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