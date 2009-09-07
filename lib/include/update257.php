<?php

function Update257() {
global $classes, $Options;
//241
$auth = TAuthDigest::Instance();
$auth->Data['cookie'] = '';
$auth->Data['cookieenabled'] = false;
$auth->Data['cookieexpired'] = 0;
$auth->Data['xxxcheck'] = true;
$auth->Save();

if (!isset($classes->items['TAdminLogin'])) $classes->Add('TAdminLogin', 'adminlogin.php');

//251
$Options->Lock();
$Options->commentpages = true;
$Options->commentsperpage = 100;
$Options->commentsdisabled = false;
$Options->Unlock();

$Template = TTemplate::Instance();
      $Template ->theme = parse_ini_file($Template ->path . 'theme.ini', true);
      $Template ->Save();

$Template ->basename = 'template.pda';
$Template ->Load();
      $Template ->theme = parse_ini_file($Template ->path . 'theme.ini', true);
      $Template ->Save();

$tc = TTemplateComment ::Instance();
$tc->ThemeChanged();

    $tc->basename = 'templatecomment.pda';
$tc->Load();
$tc->ThemeChanged();

//253
$rss = TRSS::Instance();
$rss->Data['template'] = '';
$rss->Save();

//254
$Urlmap = TUrlmap::Instance();
unset($Urlmap->get['/comments/subscribe/']);
$Urlmap->Save();
//257
$classes->Add('TManifest', 'manifest.php');
}
?>