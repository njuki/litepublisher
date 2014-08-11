<?php
function update592() {
$db = tmetapost::i()->db;
    $db->delete("name = 'pinged'");
}