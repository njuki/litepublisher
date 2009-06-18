<?php

function TRobotstxtInstall(&$self) {
 global $Options;
 $Urlmap = &TUrlmap::Instance();
 $Urlmap->Add('/robots.txt', get_class($self), null);
 
 $self->Lock();
 $self->Add("#$Options->url$Options->home");
 $self->Add('User-agent: *');
 $self->AddDisallow('/rss/');
 $self->AddDisallow('/comments/');
 $self->AddDisallow('/admin/');
 $self->Unlock();
}

function TRobotstxtUninstall(&$self) {
 TUrlmap::unsub($self);
}

?>