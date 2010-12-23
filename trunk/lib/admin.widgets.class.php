<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminwidgets extends tadminmenu {
  
  public static function instance($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public static function getsidebarnames(tview $view) {
    $count = $view->theme->sidebarscount;
    $result = range(1, $count);
    $parser = tthemeparser::instance();
    $about = $parser->getabout($view->theme->name);
    foreach ($result as $key => $value) {
      if (isset($about["sidebar$key"])) $result[$key] = $about["sidebar$key"];
    }
    return $result;
  }
  
  public static function getsidebarsform() {
    $idview = tadminhtml::getparam('idview', 1);
    $view = tview::instance($idview);
    $widgets = twidgets::instance();
    $html = tadminhtml ::instance();
    $html->section = 'widgets';
    $lang = tlocal::instance('widgets');
    $args = targs::instance();
    $args->idview = $idview;
    $result = $html->formhead();
    $args->adminurl = tadminhtml::getadminlink('/admin/views/widgets/', 'idwidget');
    $count = count($view->sidebars);
    $sidebarnames = self::getsidebarnames($view);
    foreach ($view->sidebars as $i => $sidebar) {
      $orders = range(1, count($sidebar));
      foreach ($sidebar as $j => $_item) {
        $id = $_item['id'];
        $item = $widgets->getitem($id);
        $args->id = $id;
        $args->ajax = $_item['ajax'];
        $args->inline = $_item['ajax'] === 'inline';
        $args->disabled = ($item['cache'] == 'cache') || ($item['cache'] == 'nocache') ? '' : 'disabled';
        $args->add($item);
        $args->sidebarcombo = tadminhtml::getcombobox("sidebar-$id", $sidebarnames, $i);
        $args->ordercombo = tadminhtml::getcombobox("order-$id", $orders, $j);
        $result .= $html->item($args);
      }
    }
    $result .= $html->formfooter();
    
    //all widgets
    $result .= $html->addhead();
    foreach ($widgets->items as $id => $item) {
      $args->id = $id;
      $args->add($item);
      $args->checked = tsidebars::getpos($view->sidebars, $id) ? false : true;
      $result .= $html->additem($args);
    }
    $result .= $html->addfooter();
    return  $html->fixquote($result);
  }
  
  // parse POST into sidebars array
  public static function editsidebars(array &$sidebars) {
    // collect all id from checkboxes
    $items = array();
    foreach ($_POST as $key => $value) {
      if (strbegin($key, 'widgetcheck-'))$items[] = (int) $value;
    }
    
    foreach ($items as $id) {
      if ($pos = tsidebars::getpos($sidebars, $id)) {
        list($i, $j) = $pos;
        if (isset($_POST['deletewidgets']))  {
          array_delete($sidebars[$i], $j);
        } else {
          $i2 = (int)$_POST["sidebar-$id"];
          $j2 = (int) $_POST["order-$id"];
          if ($j2 > count($sidebars[$i2])) $j2 = count($sidebars[$i2]);
          if (($i != $i2) || ($j != $j2)) {
            $item = $sidebars[$i][$j];
            array_delete($sidebars[$i], $j);
            array_insert($sidebars[$i2], $item, $j2);
          }
          $sidebars[$i2][$j2]['ajax'] =  isset($_POST["inlinecheck-$id"]) ? 'inline' : isset($_POST["ajaxcheck-$id"]);
        }
      }
    }
    //    return $this->html->h2->success;
  }
  
  public function getcontent() {
    switch ($this->name) {
      case 'widgets':
      $idwidget = tadminhtml::getparam('idwidget', 0);
      $widgets = twidgets::instance();
      if ($widgets->itemexists($idwidget)) {
        $widget = $widgets->getwidget($idwidget);
        return  $widget->admin->getcontent();
      } else {
        $idview = tadminhtml::getparam('idview', 1);
        $view = tview::instance($idview);
        $result = tadminviews::getviewform('/admin/views/widgets/');
        if (($idview == 1) || $view->customsidebar) {
          $result .= self::getsidebarsform();
        } else {
          $args = targs::instance();
          $args->idview = $idview;
          $args->customsidebar = $view->customsidebar;
          $args->disableajax = $view->disableajax;
          $args->action = 'options';
          $result .= $this->html->adminform('[checkbox=customsidebar] [checkbox=disableajax] [hidden=idview] [hidden=action]', $args);
        }
        return $result;
      }
      
      case 'addcustom':
      $widget = tcustomwidget::instance();
      return  $widget->admin->getcontent();
    }
  }
  
  public function processform() {
    litepublisher::$urlmap->clearcache();
    switch ($this->name) {
      case 'widgets':
      $idwidget = (int) tadminhtml::getparam('idwidget', 0);
      $widgets = twidgets::instance();
      if ($widgets->itemexists($idwidget)) {
        $widget = $widgets->getwidget($idwidget);
        return  $widget->admin->processform();
      } else {
        if (isset($_POST['action'])) self::setsidebars();
        return $this->html->h2->success;
      }
      
      case 'addcustom':
      $widget = tcustomwidget::instance();
      return  $widget->admin->processform();
    }
  }
  
  public static function setsidebars() {
    $idview = (int) tadminhtml::getparam('idview', 1);
    $view = tview::instance($idview);
    
    switch ($_POST['action']) {
      case 'options':
      $view->disableajax = isset($_POST['disableajax']);
      $view->customsidebar = isset($_POST['customsidebar']);
      break;
      
      case 'edit':
      self::editsidebars($view->sidebars);
      break;
      
      case 'add':
      $widgets = twidgets::instance();
      foreach ($_POST as $key => $value) {
        if (strbegin($key, 'addwidget-')){
          $id = (int) $value;
          if (!$widgets->itemexists($id) || $widgets->subclass($id)) continue;
          $view->sidebars[0][] = array(
          'id' => $id,
          'ajax' => false
          );
        }
      }
    }
    $view->save();
  }
  
}//class

class tsidebars extends tdata {
  public $items;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $view = tview::instance();
    $this->items = &$view->sidebars;
  }
  
public function load() {}
  
  public function save() {
    tview::instance()->save();
  }
  
  public function add($id) {
    $this->insert($id, false, 0, -1);
  }
  
  public function insert($id, $ajax, $index, $order) {
    if (!isset($this->items[$index])) return $this->error("Unknown sidebar $index");
    $item = array('id' => $id, 'ajax' => $ajax);
    if (($order < 0) || ($order > count($this->items[$index]))) {
      $this->items[$index][] = $item;
    } else {
      array_insert($this->items[$index], $item, $order);
    }
    $this->save();
  }
  
  public function delete($id, $index) {
    if ($i = $this->indexof($id, $index)) {
      array_delete($this->items[$index], $i);
      $this->save();
      return $i;
    }
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
  
  public static function getpos(array &$sidebars, $id) {
    foreach ($sidebars as $i => $sidebar) {
      foreach ($sidebar as $j => $item) {
        if ($id == $item['id']) return array($i, $j);
      }
    }
    return false;
  }
  
  public static function setpos(array &$items, $id, $newsidebar, $neworder) {
    if ($pos = self::getpos($items, $id)) {
      list($oldsidebar, $oldorder) = $pos;
      if (($oldsidebar != $newsidebar) || ($oldorder != $neworder)){
        $item = $items[$oldsidebar][$oldorder];
        array_delete($items[$oldsidebar], $oldorder);
        if (($neworder < 0) || ($neworder > count($items[$newsidebar]))) $neworder = count($items[$newsidebar]);
        array_insert($items[$newsidebar], $item, $neworder);
      }
    }
  }
  
public static function fix() {
$widgets = twidgets::instance();
foreach ($widgets->classes as $classname => &$items) {
foreach ($items as $i => $item) {
if (!isset($widgets->items[$item['id']])) unset($items[$i]);
}
}

$views = tviews::instance();
foreach ($views->items as &$viewitem) {
if (($viewitem['id'] != 1) && !$viewitem['customsidebar']) continue;
unset($sidebar);
foreach ($viewitem['sidebars'] as &$sidebar) {
for ($i = count($sidebar) - 1; $i >= 0; $i--) {
echo $sidebar[$i]['id'], '<br>';
          if (!isset($widgets->items[$sidebar[$i]['id']])) {
array_delete($sidebar, $i);
}
}
}
}
$views->save();
}

}//class

?>