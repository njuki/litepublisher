<?php

function TTemplatePostInstall(&$self) {
 $Template = &TTemplate::Instance();
 $Template->Lock();
 $Template->AddTag('postscript', get_class($self), 'GetPostscript');
 $Template->Unlock();
}

function TTemplatePostUninstall(&$self) {
 $Template = &TTemplate::Instance();
 $Template->DeleteTagClass(get_class($self));
}

?>