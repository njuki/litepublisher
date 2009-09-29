<?php

function Update272() {
global $paths;
require($paths['lib'] . 'update' . DIRECTORY_SEPARATOR . 'ini2tml.php');
$list = TFiler::GetDirList($paths['themes']);
foreach ($list as $dir) {
ini2tml($paths['themes'] .$dir . DIRECTORY_SEPARATOR);
}

$form = TCommentForm::Instance();
$tc = TTemplateComment::Instance();
unset($tc->Data['items']);
$tc->ThemeChanged();

unset($form->Data['items']);
unset($form->Data['fields']);
unset($form->Data['Hidden']);
$form->unlock();
}

?>