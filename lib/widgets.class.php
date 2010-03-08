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
  public static $default;
  
  public static function instance($id = null) {
    if (is_null($id)) {
      $id = isset(self::$default) ? self::$default : 0;
    }
    return parent::instance(__class__, $id);
  }
  
  protected function create() {
    parent::create();
    $this->addevents('onsitebar');
    $this->current = 0;
    $theme = ttheme::instance();
    $this->count = $theme->sitebarscount;
    $this->dbversion = false;
    $this->addmap('items', array(0 => array(), 1 => array(), 2 => array()));
  }
  
  public function getbasename() {
    return 'widgets' . DIRECTORY_SEPARATOR  . $this->id;
  }
  
  public function load() {
    if (!isset($this->id)) return false;
    if ($this->id !== 0) return parent::load();
    
    //значит id = 0 и сайтбары по умолчанию будем хранить в ttemplate
    $template = ttemplate::instance();
    if (isset($template->data['sitebars'])) {
      $this->data = &$template->data['sitebars'];
    } else {
      $template->data['sitebars'] = &$this->data;
    }
    $this->afterload();
  }
  
  public function save() {
    if ($this->id !== 0) return parent::save();
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
    $theme = ttheme::instance();
    $file = litepublisher::$paths->cache . "$theme->name.$theme->tmlfile.sitebar.$this->id.$this->current.php";
    if (file_exists($file)) {
      $result = file_get_contents($file);
    } else {
      $result = $this->getsitebar($this->current);
      //если закончились сайтбары, то остатки объеденить
      if ($this->count == $this->current + 1) {
        for ($i = $this->current + 1; $i < count($this->items); $i++) {
          $result .= $this->getsitebar($i);
        }
      }
      
      file_put_contents($file, $result);
      @chmod($file, 0666);
    }
    $this->callevent('onsitebar', array(&$result, $this->current++));
    return $result;
  }
  
  private function getsitebar($index) {
    $result = '';
    $template = ttemplate::instance();
    $i = 0;
    foreach ($this->items[$index] as $id => $item) {
      $this->curwidget= $id;
      $this->curindex= $i++;
      $content = $this->getwidgetcontent($item);
      $template->onwidget($id, $content);
      $result .= $content;
    }
    return $result;
  }
  
  public function getcachefilename($id) {
    return "widget.$this->id.$id.php";
  }
  
  public function getcachefile($id) {
    return litepublisher::$paths->cache . $this->getcachefilename($id);
  }
  
  public function getwidget($id) {
    return $this->getwidgetcontent($this->getitem($id));
  }
  
  private function getwidgetcontent($item) {
    switch ( $item['echotype']) {
      case 'echo':
      $result = $this->dogetwidget($item);
      break;
      
      case 'include':
      $filename = $this->getcachefilename($item['id']);
      $file = litepublisher::$paths->cache . $filename;
      if (!@file_exists($file)) {
        $result = $this->dogetwidget($item);
        file_put_contents($file, $result);
        @chmod($file, 0666);
      }
      $result = "\n<?php @include(litepublisher::\$paths->cache . '$filename'); ?>\n";
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
    if (!@class_exists($item['class'])) {
      $this->deleteclass($item['class']);
      return '';
    }
    
    $result = '';
    $template = ttemplate::instance();
    $widget = GetInstance($item['class']);
    try {
      if (empty($item['template'])) {
        $result =   $widget->getwidget($item['id'], $this->current);
      }else {
        $content = $widget->getwidgetcontent($item['id'], $this->current);
        $template->onwidgetcontent($id, $content);
        $theme= ttheme::instance();
        $result = $theme->getwidget($item['title'], $content, $item['template'], $this->current);
      }
    } catch (Exception $e) {
      litepublisher::$options->handexception($e);
    }
    return $result;
  }
  
  public function add($class, $echotype, $sitebar, $order) {
    return $this->addext($class, $echotype, '', '', $sitebar, $order);
  }
  
  public function addext($class, $echotype, $template, $title, $sitebar, $order) {
    if ($sitebar >= $this->count) return $this->error("sitebar index $sitebar cant more than sitebars count in theme");
    if (!isset($this->items[$sitebar])) return $this->error("Unknown sitebar $sitebar");
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
    $id = $item['id'];
    //вставить в массив с соблюдением порядка и ключей
    if (!isset($this->items[$sitebar])) $sitebar = count($this->items) - 1;
    if ($order < 0) $order = 0;
    if ($order >= count($this->items[$sitebar])) {
      $this->items[$sitebar][$id] = $item;
    } else {
      $i = 0;
      $new = array();
      foreach ($this->items[$sitebar] as $idwidget => $widget) {
        if ($i++ == $order) $new[$id] = $item;
        $new[$idwidget] = $widget;
      }
      $this->items[$sitebar] = $new;
    }
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
  
  public function getorder($id) {
    $result = 0;
    $sitebar = $this->findsitebar($id);
    foreach ($this->items[$sitebar] as $idwidget => $item) {
      if ($id == $idwidget) break;
      $result++;
    }
    return $result;
  }
  
  public static function  expired($instance) {
    $self = self::instance(0);
    $self->setexpired(get_class($instance));
  }
  
  public function setexpired($class) {
    for ($i = count($this->items) - 1; $i >= 0; $i--) {
      foreach ($this->items[$i] as $id => $item) {
        if ($class == $item['class'])  {
          if ($item['echotype'] == 'echo') {
            $urlmap = turlmap::instance();
            $urlmap->clearcache();
            return;
          } else {
            @unlink($this->getcachefile($item['id']));
          }
        }
      }
    }
  }
  
  public function itemexpired($id) {
    $item = $this->getitem($id);
    if ($item['echotype'] == 'echo') {
      $urlmap = turlmap::instance();
      $urlmap->clearcache();
    } else {
      @unlink($this->getcachefile($item['id']));
    }
  }
  
  public function changesitebar($id, $sitebar) {
    $this->setpos($id, $sitebar, $this->getorder($id));
  }
  
  public function changeorder($id, $order) {
    $this->setpos($id, $this->findsitebar($id), $order);
  }
  
  public function setpos($id, $sitebar, $order) {
    $oldsitebar = $this->findsitebar($id);
    $oldorder = $this->getorder($id);
    if (($oldsitebar == $sitebar) && ($oldorder == $order)) return;
    $item = $this->items[$oldsitebar][$id];
    unset($this->items[$oldsitebar][$id]);
    $this->insert($item, $sitebar, $order);
  }
  
}//class
?>