<?php

function TLinksWidgetInstall(&$self) {
 $lang = TLocal::$data['blogolet'];
 $self->Add($lang['url'], $lang['description'], $lang['name']);
 
 $Urlmap = &TUrlmap::Instance();
 $Urlmap->AddGet($self->redirlink, get_class($self), null);
 
 $robots = &TRobotstxt ::Instance();
 $robots->AddDisallow($self->redirlink);
 $robots->Save();
}

function TLinksWidgetUninstall(&$self) {
 $Template = &TTemplate::Instance();
 $Template->DeleteWidget(get_class($self));
 TUrlmap::unsub($self);
}

?>