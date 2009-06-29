<?php

function Update234() {
$cf = &TCommentForm::Instance();
$cf->Data['confirmtemplate'] = '';
$cf->Save();
}

?>