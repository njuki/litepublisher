<?php

function tcommentsInstall($self) {
$self->options = array('recentcount' =>  7,
'SendNotification' =>  true);

    $manager = TDBManager ::instance();
    $dir = dirname(__file__) . DIRECTORY_SEPARATOR;
    $manager->CreateTable($self->table, file_get_contents($dir .'comments.sql'));
    $manager->CreateTable($self->rawtable, file_get_contents($dir .'raw.sql'));
}

?>