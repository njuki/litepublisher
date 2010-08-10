<?php
function update363() {
$redir = tredirector::instance();
$redir->lock();
$redir->add('/$post.link', '/');
$redir->unlock();

$widget = ttagswidget::instance();
$widgets = twidgets::instance();
if (!$widgets->find($widget)) {
$widgets->add($widget);
}
litepublisher::$urlmap->lock();
$events = &litepublisher::$urlmap->data['events'];
if (isset($events['CacheExpired'])) {
$events['onclearcache'] = $events['CacheExpired'];
unset($events['CacheExpired']);
}
    $cache = twidgetscache::instance();
litepublisher::$urlmap->onclearcache = $cache->onclearcache;
litepublisher::$urlmap->unlock();

$widget = tcommentswidget::instance();
  litepublisher::$classes->commentmanager->changed = $widget->changed;
  }

?>