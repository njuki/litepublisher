<?php
$mode = 'update';
include('index.php');
    $updater = &TUpdater::Instance();
echo $updater->AutoUpdate();
?>