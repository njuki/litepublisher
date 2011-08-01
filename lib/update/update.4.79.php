<?php

function update479() {
if (dbversion) {
$man = tdbmanager::instance();
$man->alter('categories', "modify `title` text NOT NULL");
$man->alter('tags', "modify `title` text NOT NULL");
}
}