<?php

function TTemplateCommentInstall(&$self) {
  global $paths;
  $Template = TTemplate::Instance();
  $Template->Lock();
  $Template->AddTag('comments', get_class($self), 'GetComments');
  $Template->AddTag('CommentsCountLink', get_class($self), 'GetCommentsCountLink');
  $Template->ThemeChanged = $self->ThemeChanged;
  $Template->Unlock();
  
  SafeSaveFile($paths['data'].$Template->GetBaseName() . '.pda', $Template->SaveToString());
  SafeSaveFile($paths['data'].$self->GetBaseName() . '.pda', $self->SaveToString());
}

function TTemplateCommentUninstall(&$self) {
  $Template = &TTemplate::Instance();
  $Template->Lock();
  $Template->DeleteTagClass(get_class($self));
  $Template->UnsubscribeClass($self);
  $Template->Unlock();
}

?>