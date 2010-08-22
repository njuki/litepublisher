<?php
function update371() {
$redir = tredirector::instance();
$redir->lock();
  $redir->add('/wp-rss.php', '/rss.xml');
  $redir->add('/wp-rss2.php', '/rss.xml');
$redir->add('/wp-login.php', '/admin/login/');

$redir->unlock();
}?>