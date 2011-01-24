<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminsingletagwidget  extends tadminwidget {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function getcontent() {
    $widget = tsingletagwidget::instance();
    $args = targs::instance();
    $id = (int) tadminhtml::getparam('idwidget', 0);
    if (isset($widget->items[$id])) {
      $item = $widget->items[$id];
      $args->mode = 'edit';
    } else {
      $id = 0;
      $args->mode = 'add';
      $item = array(
      'title' => '',
      'content' => '',
      'template' => 'widget'
      );
    }
    
    $args->idwidget = $id;
    $html= $this->html;
    $args->text = $item['content'];
    $args->template =tadminhtml::array2combo(self::gettemplates(), $item['template']);
    $result = $this->optionsform($item['title'], $html->parsearg(
    '[editor=text]
    [combo=template]
    [hidden=mode]
    [hidden=idwidget]',
    $args));
    $result .= $html->customheader();
    $args->adminurl = $this->adminurl;
    
    foreach ($widget->items as $id => $item) {
      $args->idwidget = $id;
      $args->add($item);
      $result .= $html->customitem($args);
    }
    $result .= $html->customfooter();
    return $result;
  }
  
  public function processform()  {
    $widget = $this->widget;
    if (isset($_POST['mode'])) {
      extract($_POST, EXTR_SKIP);
      switch ($mode) {
        case 'add':
        $_GET['idwidget'] = $widget->add($title, $text, $template);
        break;
        
        case 'edit':
        $id = isset($_GET['idwidget']) ? (int) $_GET['idwidget'] : 0;
        if ($id == 0) $id = isset($_POST['idwidget']) ? (int) $_POST['idwidget'] : 0;
        $widget->edit($id, $title, $text, $template);
        break;
      }
    } else {
      $widgets = twidgets::instance();
      $widgets->lock();
      $widget->lock();
      foreach ($_POST as $key => $value) {
        if (strbegin($key, 'widgetcheck-')) $widget->delete((int) $value);
      }
      $widget->unlock();
      $widgets->unlock();
    }
  }
  

}//class