<?php
function update388() {
$redir = tredirector::instance();
$redir->lock();
$redir->add('/themes/default/print.css', '/themes/default/css/style.css');
$redir->add('/themes/default/style.css', '/themes/default/css/style.css');
$redir->unlock();
}
?>