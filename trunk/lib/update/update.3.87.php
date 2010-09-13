<?php
function update387() {
$redir = tredirector::instance();
$redir->add('/rss/', '/rss.xml');$redir->save();
$redir->add('/profile/', '/profile.htm');
$redir->add('/profile.htm', '/');
$redir->add('/foaf/', '/foaf.xml');
$redir->save();
}
?>