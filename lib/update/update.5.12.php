<?php

function update512() {
if (dbversion) {
$man = tdbmanager::i();
$man->alter('posts', "add   `idperm` int unsigned NOT NULL default '0' after author");
}

litepublisher::$classes->add('tpostpassword', 'post.password.class.php');
}
