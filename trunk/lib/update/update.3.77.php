<?php
function update377() {
if (dbversion) {
$man = tdbmanager ::instance();
$man->alter('comments', "alter ip set default ''");
$man->alter('comusers', "alter ip set default ''");
}
}
?>