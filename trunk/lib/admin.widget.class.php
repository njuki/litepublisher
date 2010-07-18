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

public function getcontent(){
$this->error('Not implemented');
}

  public function processform()  {
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

  public function getcontent(){
$widget = $this->widget;
    $args = targs::instance();
$args->showcount = $widget->showcount;
$args->maxcount = $widget->maxcount;
$args->combo = tadminwidgets::getcombo(tlocal::$data['sortnametags'], 'sort', $widget->sortname);
return $this->optionsform($this->html->tagsform($args));
}

  public function processform()  {
extract($_POST);
$widget = $this->widget;
$widget->lock();
$widget->title = $title;
$widget->maxcount = int) $maxcount;
$widget->showcount = isset($showcount);
$widget->sortname = $sort;
$widget->unlock();
}

}//class

class tadminpostswidget extends tadminwidget {

  public static function instance($id = null) {
    return getinstance(__class__);
  }

  public function getcontent(){
$widget = $this->widget;
    $args = targs::instance();
$args->maxcount = $widget->maxcount;
return $this->optionsform($this->html->postsform($args));
}

  public function processform()  {
extract($_POST);
$widget = $this->widget;
$widget->lock();
$widget->title = $title;
$widget->maxcount = int) $maxcount;
$widget->unlock();
}

}//class



?>