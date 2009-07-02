<?php

function Update236() {
unset($GLOBALS['Template']);
unset(TClasses::$instances['TTemplate']);
$templ= &TTemplate::Instance();
$tc = &TTemplateComment::Instance();
$templ->ThemeChanged = $tc->ThemeChanged;
//$tc->Save();
}

?>