<?php

function TXMLRPCPingbackInstall(&$self) {
 $Caller = &TXMLRPC::Instance();
 $Caller->Add('pingback.ping', 'ping', get_class($self));
}

?>