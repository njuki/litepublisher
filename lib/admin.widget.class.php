<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminwidget extends tdata {
public $widget;
protected $html;
protected $lang;

protected function create() {
parent::instance();
$this->html = THtmlResource ::instance();
    $this->html->section = 'widgets';
    $this->lang = tlocal::instance('widgets');
}


  protected function dogetcontent(twidget $widget, targs $args){
$this->error('Not implemented');
}

public function getcontent(){
$form = $this->dogetcontent($this->widget, targs::instance());
return $this->optionsform($form);
}

  public function processform()  {
$widget = $this->widget;
$widget->lock();
if (isset($_POST['title'])) $widget->title = $_POST['title'];
$this->doprocessform($widget);
$widget->unlock();
return $this->html->h2->updated;
}

  protected function doprocessform(twidget $widget)  {
$this->error('Not implemented');
}

protected function optionsform($content) {
    $args = targs::instance();
$args->title = $this->widget->title;
$args->content = $content;
return $this->html->optionsform($args);
}

}//class

class tadmintagswidget extends tadminwidget {

  public static function instance($id = null) {
    return getinstance(__class__);
  }

  protected function dogetcontent(twidget $widget, targs $args){
$args->showcount = $widget->showcount;
$args->maxcount = $widget->maxcount;
$args->combo = tadminwidgets::getcombo(tlocal::$data['sortnametags'], 'sort', $widget->sortname);
return $this->html->tagsform($args);
}

  protected function doprocessform(twidget $widget)  {
extract($_POST);
$widget->maxcount = int) $maxcount;
$widget->showcount = isset($showcount);
$widget->sortname = $sort;
}

}//class

class tadminmaxcount extends tadminwidget {

  public static function instance($id = null) {
    return getinstance(__class__);
  }

  protected function dogetcontent(twidget $widget, targs $args){
$args->maxcount = $widget->maxcount;
return $this->html->maxcountform($args);
}

  protected function doprocessform(twidget $widget)  {
$widget->maxcount = int) $_POST['maxcount'];
}

}//class

class tadminshowcount extends tadminwidget {

  public static function instance($id = null) {
    return getinstance(__class__);
  }

  protected function dogetcontent(twidget $widget, targs $args){
$args->showcount = $widget->showcount;
return $this->html->showcountform($args);
}

  protected function doprocessform(twidget $widget)  {
$widget->showcount = isset($_POST['showcount']);
}

}//class

class tadminfriendswidget extends tadminwidget {

  public static function instance($id = null) {
    return getinstance(__class__);
  }

  protected function dogetcontent(twidget $widget, targs $args){
$args->maxcount = $widget->maxcount;
$args->redir = $widget->redir;
return $this->html->friendsform($args);
}

  protected function doprocessform(twidget $widget)  {
$widget->maxcount = (int) $_POST['maxcount']);
$widget->redir = isset($_POST['redir']);
}

}//class

class tadminorderwidget extends tadminwidget {

  public static function instance($id = null) {
    return getinstance(__class__);
  }

  protected function dogetcontent(twidget $widget, targs $args){
$widgets =twidgets::instance();
$item = &$widgets->finditem($widget->id);
if ($item) {
$args->sitebarcombo = tadminwidgets::getcombo(tadminwidgets::getsitebarnames(3), 'sitebar', $item['sitebar']);
$args->ordercombo = tadminwidgets::getcombo(range(-1, 10),  'order', $item['order']);
return $this->html->locationform($args);
}
}

  protected function doprocessform(twidget $widget)  {
$widgets = twidgets::instance();
$item = &$widgets->finditem($widget->id);
if ($item) {
$item['sitebar'] = (int) $_POST['sitebar'];
$item['order'] = ((int) $_POST['order']) - 1;
}
}

}//class

class tadmincustomwidget extends tadminwidget {

  public static function instance($id = null) {
    return getinstance(__class__);
  }

public static function gettemplates() {
$result = array();
$lang = tlocal::instance('widgets');
$result['widget'] = $lang->defaulttemplate;
foreach (tthemeparser::getwidgetnames() as $name) {
$result[$name] = $lang->$name;
}
return $result;
}

public function getcontent() {
$widget = $this->widget;
$id = $_GET['idwidget'];
$item = $widget->getitem($id);
    $args = targs::instance();
$html= $this->html;
$args->title = $item['title'];
$args->text = $item['content'];
$args->combo =tadminwidgets::getcombo(self::gettemplates(), 'template', $item['template']);
$args->content = $html->customform($args);
$result = $html->optionsform($args);

      $list = '';
      $args->adminurl = litepublisher::$options->url . litepublisher::$options->q . 'idwidget';
      foreach ($widget->items as $id => $item) {
        $args->idwidget = $id;
        $args->add($item);
        $list .= $html->customitem($args);
      }
      $result .= sprintf($html->customitems, $list);
return $result;
}

  public function processform()  {
extract($_POST);
$idwidget = (int) $_GET['idwidget'];
$widget = $this->widget;
$widget->add($title, $text, $template);
$widget->edit($idwidget, $title, $text, $template);
}

}//class
