<?php

function Update272() {
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