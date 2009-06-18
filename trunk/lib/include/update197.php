<?php

function InstallFoaf() {
 TClasses::Lock();
 TClasses::Register('TXMLRPCOpenAction', 'xmlrpc-openaction.php');
 TClasses::Register('TFoaf', 'foafclass.php');
 TClasses::Register('TFoafManager', 'foafmanager.php');
 TClasses::Register('TProfile', 'profileclass.php');
 TClasses::Register('TAdminFoaf', 'adminfoaf.php');
 TClasses::Unlock();
 
 $Template = &TTemplate::Instance();
 $Template->AddWidget('TFoaf', 'echo', -1, 0);
}

function Update197() {
 global $domain;
 $redir = &TRedirector ::Instance();
 $redir->Add('/files/sitemap.1.xml.gz', '/files/$domain.1.xml.gz');
 InstallFoaf();
}
?>