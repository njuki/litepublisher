<?php

function TSitemapInstall(&$self) {
 global $Options;
 $cron = &TCron::Instance();
 $cron->Add('day', get_class($self),  'Cron', null);
 $Urlmap = &TUrlmap::Instance();
 $Urlmap->Add('/sitemap.xml', get_class($self), 'xml');
 $Urlmap->Add('/sitemap/', get_class($self), null);
 
 $robots = &TRobotstxt ::Instance();
 array_splice($robots->items, 1, 0, "Sitemap: $Options->url/sitemap.xml");
 $robots->Save();
 
 $self->CreateFiles();
}

function TSitemapUninstall(&$self) {
 TUrlmap::unsub($self);
}

?>