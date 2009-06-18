<?php

function TTemplateCommentInstall(&$self) {
 $Template = &TTemplate::Instance();
 $Template->Lock();
 $Template->AddTag('comments', get_class($self), 'GetComments');
 $Template->AddTag('CommentsCountLink', get_class($self), 'GetCommentsCountLink');
 $Template->Unlock();
}

function TTemplateCommentUninstall(&$self) {
 $Template = &TTemplate::Instance();
 $Template->DeleteTagClass(get_class($self));
}

?>