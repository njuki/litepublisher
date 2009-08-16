<?php

function Update251() {
global $Options;
$Options->Lock();
$Options->commentpages = true;
$Options->commentsperpage = 100;
$Options->Unlock();

$Template = TTemplate::Instance();
      $Template ->theme = parse_ini_file($Template ->path . 'theme.ini', true);
      $Template ->Save();

$tc = TTemplateComment ::Instance();
$tc->ThemeChanged();
}
?>