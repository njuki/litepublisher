<?php

function TTemplateCommentInstall(&$self) {
 $Template = &TTemplate::Instance();
 $Template->Lock();
 $Template->AddTag('comments', get_class($self), 'GetComments');
 $Template->AddTag('CommentsCountLink', get_class($self), 'GetCommentsCountLink');
 $Template->ThemeChanged = $self->ThemeChanged;
 $Template->Unlock();
}

function TTemplateCommentUninstall(&$self) {
 $Template = &TTemplate::Instance();
 $Template->Lock();
 $Template->DeleteTagClass(get_class($self));
 $Template->UnsubscribeClass($self);
 $Template->Unlock();
}

?>