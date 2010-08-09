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

  }

?>