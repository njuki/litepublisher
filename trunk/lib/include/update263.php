<?php

function Update263() {
global $Options;
$Options->Lock();
$Options->echoexception = false;
$Options->version = '2.63';
$Options->Unlock();

    @header("Location: $Options->url/admin/service/?update=1");
    exit();
}

?>