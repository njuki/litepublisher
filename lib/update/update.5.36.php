<?php

function update536() {
$man = tdbmanager::i();
$man->alter('posts', 'change head   rawhead text NOT NULL');
}