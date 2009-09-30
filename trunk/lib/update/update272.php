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
if (isset($tc->Data['commentsini'])) unset($tc->Data['commentsini']);
$tc->ThemeChanged();

if (isset($form->Data['items'])) unset($form->Data['items']);
if (isset($form->Data['fields'])) unset($form->Data['fields']);
if (isset($form->Data['Fields'])) unset($form->Data['Fields']);
if (isset($form->Data['lastid'])) unset($form->Data['lastid']);
if (isset($form->Data['Hidden'])) unset($form->Data['Hidden']);
$form->unlock();
}

?>