<?php

function update466() {
if (dbversion) {
$man = tdbmanager::instance();
$man->alter('comusers', "modify `name` text NOT NULL");
$man->alter('pingbacks', "modify `title` text NOT NULL");
}
}
