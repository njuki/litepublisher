<?php
function update389() {
$redir = tredirector::instance();
$redir->lock();
$redir->add('/profile/', '/profile.htm');
$redir->add('/sitemap/', '/sitemap.htm');
$redir->unlock();
}
?>