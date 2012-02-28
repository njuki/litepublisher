<?php

function update516() {
$views = tviews::i();
foreach ($views->items as &$item) {
$item['class'] = 'tview';
}
$views->save();
}