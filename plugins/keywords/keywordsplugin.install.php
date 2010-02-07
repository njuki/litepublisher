<?php

function TKeywordsPluginInstall(&$self) {
  global $paths;
  @mkdir($paths['data'] . 'keywords', 0777);
  @chmod($paths['data'] . 'keywords', 0777);
  
  $Template->AddWidget(get_class($self), 'nocache', '', '',  -1, $Template->sitebarcount - 1);
  $Urlmap = &TUrlmap::Instance();
  $Urlmap->AfterRequest = $self->ParseRef;
 }
 
function TKeywordsPluginUninstall(&$self) {
  global $paths;
  TUrlmap::unsub($self);
  $Template = &TTemplate::Instance();
  $Template->DeleteWidget(get_class($self));
  //TFiler::DeleteFiles($paths['data'] . 'keywords' . DIRECTORY_SEPARATOR  , true);
 }

?>