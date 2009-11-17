<?php

function TTemplatePostInstall($self) {
  global $paths;
  $Template = &TTemplate::Instance();
  $Template->Lock();
  $Template->AddTag('postscript', get_class($self), 'GetPostscript');
  $Template->Unlock();
  
  SafeSaveFile($paths['data'].$Template->GetBaseName() . '.pda', $Template->SaveToString());
}

function TTemplatePostUninstall(&$self) {
  $Template = &TTemplate::Instance();
  $Template->DeleteTagClass(get_class($self));
}

?>