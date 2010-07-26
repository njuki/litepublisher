<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tkeywordspluginInstall($self) {
  @mkdir(litepublisher::$paths->data . 'keywords', 0777);
  @chmod(litepublisher::$paths->data . 'keywords', 0777);
  
  $item = litepublisher::$classes->items[get_class($self)];
  litepublisher::$classes->add('tkeywordswidget','keywords.widget.php', $item[1]);
  
  $about = tplugins::getabout(tplugins::getname(__file__));

  $widget = tkeywordswidget::instance();
  $widget->title =  $about['title'];
  $widget->save();
  
  $widgets = twidgets::instance();
$widgets->lock();
$sitebars = tsitebars::instance();
  $sitebars->insert($widgets->add($widget), false, 1, -1);
$widgets->unlock();
  
  $urlmap = turlmap::instance();
  $urlmap->lock();
  $Urlmap->afterrequest = $self->parseref;
  $urlmap->deleted = $self->urldeleted;
  $urlmap->unlock();
}

function tkeywordspluginUninstall($self) {
  turlmap::unsub($self);
  $widgets = twidgets::instance();
  $widgets->deleteclass('tkeywordswidget');
  litepublisher::$classes->delete('tkeywordswidget');
  //TFiler::DeleteFiles(litepublisher::$paths->data . 'keywords' . DIRECTORY_SEPARATOR  , true);
}

?>