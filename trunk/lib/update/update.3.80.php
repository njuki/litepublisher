<?php
function update380() {
if (dbversion) {
$man = tdbmanager ::instance();
$man->alter('files', "alter `samplingrate` set default 0");
$man->alter('files', "modify `title` text  NOT NULL");
$man->alter('files', "modify `description` text  NOT NULL");
$man->alter('files', "modify `keywords` text  NOT NULL");

$man->alter('users', "modify `password` varchar(32) NOT NULL");
$man->alter('users', "alter `ip` set default ''");
}
}
?>