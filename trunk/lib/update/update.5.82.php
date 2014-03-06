<?php
function update582() {
$t = ttemplate::i();
$t->heads = str_replace(
'<link rel="shortcut icon" type="image/x-icon" href="$template.icon" />',
'<link rel="shortcut icon" type="image/x-icon" href="$site.files/favicon.ico" />
<link rel="apple-touch-icon" href="$site.files/apple-touch-icon.png" />',
$t->heads);
$t->save();
$t->save();
}