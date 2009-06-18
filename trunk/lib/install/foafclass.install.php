<?php

function TFoafInstall(&$self) {
 global $Options;
 $Options->foaf = $Options->url . '/foaf.xml';
 $Urlmap = &TUrlmap::Instance();
 $Urlmap->Lock();
 $Urlmap->Add('/foaf.xml', get_class($self), 'xml');
 $Urlmap->AddGet($self->redirlink, get_class($self), 'redir');
 $Urlmap->Unlock();
 
 $robots = &TRobotstxt ::Instance();
 $robots->AddDisallow($self->redirlink);
 $robots->Save();
}

function TFoafUninstall(&$self) {
 TUrlmap::unsub($self);
 $Template = &TTemplate::Instance();
 $Template->DeleteWidget(get_class($self));
}

?>