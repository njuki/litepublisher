<?php

function update556() {
$m = tmediaparser::i();
if (!isset($m->data['alwaysresize'])) {
$m->data['alwaysresize'] = false;
$m->save();
}
}