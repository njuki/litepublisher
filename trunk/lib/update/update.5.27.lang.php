<?php

function update527lang() {
$lang = tlocal::admin('comments');
$js = array(
'del' => $lang->delete,
'edit' => $lang->edit,
'approve' => $lang->approve,
'hold' => $lang->hold,
'confirmdelete' => $lang->confirmdelete,
'confirm' => $lang->confirm,
'yesdelete' => $lang->yesdelete,
'nodelete' => $lang->nodelete,
'notdeleted' => $lang->notdeleted,
'notmoderated' => $lang->notmoderated,
'errorrecieved' => $lang->errorrecieved,
'notedited' => $lang->notedited,
);

tjsmerger::i()->addtext('moderate', 'lang', 
sprintf('var lang = $.extend(true, lang, {comments: %s});', json_encode($js)));
}