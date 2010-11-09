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
  
  public static function getcombo($name, array $items, $selected) {
    $result = sprintf('<select name="%1$s" id="%1$s">', $name);
    foreach ($items as $i => $item) {
      $result .= sprintf('<option value="%s" %s>%s</option>', $i, $i == $selected ? 'selected' : '', $item);
    }
    $result .= "</select>\n";
    return $result;
  }
  
  public static function getsitebarnames(tview $view) {
$count = count($view->sitebars);
    $result = range(1, $count);
    $parser = tthemeparser::instance();
    $about = $parser->getabout($view->theme->name);
    foreach ($result as $key => $value) {
      if (isset($about["sitebar$key"])) $result[$key] = $about["sitebar$key"];
    }
    return $result;
  }
  
  public static function getsitebarsform() {
$idview = self::getparam('idview', 1);
$view = tview::instance($idview);
    $widgets = twidgets::instance();
    $html = THtmlResource ::instance();
    $html->section = 'widgets';
    $lang = tlocal::instance('widgets');
    $args = targs::instance();
$args->idview = $idview;
    $result = $html->checkallscript;
    $result .= $html->formhead();
    $args->adminurl = self::getadminlink('/admin/views/widgets/', 'idwidget');
    $count = count($view->sitebars);
    $sitebarnames = self::getsitebarnames($view);
    foreach ($view->sitebars as $i => $sitebar) {
      $orders = range(1, count($sitebar));
      foreach ($sitebar as $j => $_item) {
        $id = $_item['id'];
        $item = $widgets->getitem($id);
        $args->id = $id;
        $args->ajax = $_item['ajax'];
        $args->inline = $_item['ajax'] === 'inline';
        $args->disabled = ($item['cache'] == 'cache') || ($item['cache'] == 'nocache') ? '' : 'disabled';
        $args->add($item);
        $args->sitebarcombo = self::getcombo("sitebar-$id", $sitebarnames, $i);
        $args->ordercombo = self::getcombo("order-$id", $orders, $j);
        $result .= $html->item($args);
      }
    }
    $result .= $html->formfooter();
    
    //all widgets
    $result .= $html->addhead();
    foreach ($widgets->items as $id => $item) {
      $args->id = $id;
      $args->add($item);
      $args->checked = tsitebars::getpos($view->sitebars, $id) ? false : true;
      $result .= $html->additem($args);
    }
    $result .= $html->addfooter();
    return  $html->fixquote($result);
  }
  
  // parse POST into sitebars array
  public static function editsitebars(array &$sitebars) {
    // collect all id from checkboxes
    $items = array();
    foreach ($_POST as $key => $value) {
      if (strbegin($key, 'widgetcheck-'))$items[] = (int) $value;
    }
    
    foreach ($items as $id) {
      if ($pos = tsitebars::getpos($sitebars, $id)) {
        list($i, $j) = $pos;
        if (isset($_POST['deletewidgets']))  {
          array_delete($sitebars[$i], $j);
        } else {
          $i2 = (int)$_POST["sitebar-$id"];
          $j2 = (int) $_POST["order-$id"];
          if ($j2 > count($sitebars[$i2])) $j2 = count($sitebars[$i2]);
          if (($i != $i2) || ($j != $j2)) {
            $item = $sitebars[$i][$j];
            array_delete($sitebars[$i], $j);
            array_insert($sitebars[$i2], $item, $j2);
          }
          $sitebars[$i2][$j2]['ajax'] =  isset($_POST["inlinecheck-$id"]) ? 'inline' : isset($_POST["ajaxcheck-$id"]);
        }
      }
    }
    //    return $this->html->h2->success;
  }
  
  public function getcontent() {
    switch ($this->name) {
      case 'widgets':
      $idwidget = self::getparam('idwidget', 0);
    $widgets = twidgets::instance();
      if ($widgets->itemexists($idwidget)) {
        $widget = $widgets->getwidget($idwidget);
        return  $widget->admin->getcontent();
      } else {
$idview = self::getparam('idview', 1);
$view = tview::instance($idview);
$result = tadminviews::getviewform();
if (($idview == 1) || $view->customsitebar) {
        $result .= self::getsitebarsform();
} else {
$args = targs::instance();
$args->idview = $idview;
$args->customsitebar = $view->customsitebar;
$args->disableajax = $view->disableajax;
$args->action = 'options';
$result .= $html->getadminform('[checkbox=customsitebar] [checkbox=disableajax] [hidden=idview] [hidden=action', $args);
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
      $idwidget = (int) self::getparam('idwidget', 0);
    $widgets = twidgets::instance();
      if ($widgets->itemexists($idwidget)) {
        $widget = $widgets->getwidget($idwidget);
        return  $widget->admin->processform();
      } else {
        if (isset($_POST['action'])) self::setsitebars();
        return $this->html->h2->success;
      }
      
      case 'addcustom':
      $widget = tcustomwidget::instance();
      return  $widget->admin->processform();
    }
  }
  
  public static function setsitebars() {
$idview = (int) self::getparam('idview', 1);
$view = tview::instance($idview);

    switch ($_POST['action']) {
case 'options':
$view->disableajax = isset($_POST['disableajax']);
$view->customsitebar = isset($_POST['customsitebar']);
break;

      case 'edit':
      self::editsitebars($view->sitebars);
      break;

      case 'add':
      $widgets = twidgets::instance();
      foreach ($_POST as $key => $value) {
        if (strbegin($key, 'addwidget-')){
          $id = (int) $value;
          if (!$widgets->itemexists($id) || $widgets->subclass($id)) continue;
          $views->sitebars[0][] = array(
          'id' => $id,
          'ajax' => false
          );
        }
      }
    }
$view->save();
  }
  
}//class

class tsitebars extends tdata {
  public $items;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
$view = tview::instance();
    $this->items = &$view->sitebars;
  }
  
public function load() {}
  
  public function save() {
    tview::instance()->save();
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
  
  public static function getpos(array &$sitebars, $id) {
    foreach ($sitebars as $i => $sitebar) {
      foreach ($sitebar as $j => $item) {
        if ($id == $item['id']) return array($i, $j);
      }
    }
    return false;
  }
  
  public static function setpos(array &$items, $id, $newsitebar, $neworder) {
    if ($pos = self::getpos($items, $id)) {
      list($oldsitebar, $oldorder) = $pos;
      if (($oldsitebar != $newsitebar) || ($oldorder != $neworder)){
        $item = $items[$oldsitebar][$oldorder];
        array_delete($items[$oldsitebar], $oldorder);
        if (($neworder < 0) || ($neworder > count($items[$newsitebar]))) $neworder = count($items[$newsitebar]);
        array_insert($items[$newsitebar], $item, $neworder);
      }
    }
  }
  
}//class

?>