<?php
function update377() {
$admin = tadminmenus::instance();
$admin->remove($admin->url2id('/admin/'));

if (dbversion) {
$man = tdbmanager ::instance();
$man->alter('comusers', "alter ip set default ''");
$man->alter('rawcomments', "add `ip` varchar(15) NOT NULL default ''");
$man->alter('comments', 'drop ip');
}
}
?>