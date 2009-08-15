<?php

function Update250() {
$Template = TTemplate::Instance();
      $Template ->theme = parse_ini_file($Template ->path . 'theme.ini', true);
      $Template ->Save();

$tc = TTemplateComment ::Instance();
$tc->ThemeChanged();
}
?>