<?php

function update510() {
$t = ttemplate::i();
$t->footer = str_replace('2011', '2012', $t->footer);
$t->save();
}