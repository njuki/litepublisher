<?php

function Update236() {
$templ= &TTemplate::Instance();
$templ->SubscribeEvent('ThemeChanged',  array(
'class' => 'TTemplateComment',
'func' => 'ThemeChanged'
));
//$tc = &TTemplateComment::Instance();
//$templ->ThemeChanged = $tc->ThemeChanged;
//$tc->Save();
}

?>