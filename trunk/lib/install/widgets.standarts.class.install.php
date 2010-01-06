<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tstdwidgetsInstall($self) {
  $urlmap = turlmap::instance();
  $urlmap->add('/stdwidget.htm', get_class($self), null, 'get');
  
  $self->lock();
  $self->meta = getmetawidget();
  $template = ttemplate::instance();
  $template->lock();
  $widgets = twidgets::instance();
  $widgets->lock();
  $widgets->deleted= $self->widgetdeleted;
  //sitebar 1
  $self->add('categories', true, 0);
  $self->add('archives', true, 0);
  $self->add('links', true, 0);
  $self->add('friends', true, 0);
  //sitebar 2
  $self->add('posts', true, 1);
  $self->add('comments', true, 1);
  $self->add('meta', true, 1);
  
  $widgets->unlock();
  $template->unlock();
  $self->unlock();
}

function tstdwidgetsUninstall($self) {
  $self->lock();
  $widgets = twidgets::instance();
  $widgets->deleteclass(get_class($self));
  $self->unlock();
}

function getmetawidget() {
  global $options;
  $theme = ttheme::instance();
  $tml = $theme->getwidgetitem('meta', $theme->sitebarscount - 1);
  $tml .= "\n";
  $lang = tlocal::instance('default');
  $result = sprintf($tml, $options->url . '/rss.xml', $lang->rss);
  $result .= sprintf($tml, $options->url . '/comments.xml', $lang->rsscomments);
  
  $tml = '<li><a href="%1$s" title="%2$s">%2$s</a></li>';
  $result .= sprintf($tml, $options->url . '/foaf.xml', $lang->foaf);
  $result .= sprintf($tml, $options->url . '/profile.htm', $lang->profile);
  $result .= sprintf($tml, $options->url . '/sitemap.htm', $lang->sitemap);
  return $result;
}

?>