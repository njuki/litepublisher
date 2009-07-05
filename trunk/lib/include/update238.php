<?php

function Update238() {
$templ= &TTemplate::Instance();
   $templ->theme = parse_ini_file($templ->path . 'theme.ini', true);
   $templ->Save();

$robot = &TRobotstxt::Instance();
$robot->AddDisallow('/pda/');

global $paths;
$pda = $paths['cache'] . 'pda' ;
@mkdir($pda, 0777);
@mkdir($pda, 0777);
}

?>