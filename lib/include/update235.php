<?php

function Update235() {
$templ= &TTemplate::Instance();
$templ->theme = parse_ini_file($templ->path . 'theme.ini', true);
$templ->Save();
}

?>