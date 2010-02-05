<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function TXMLRPCPingbackInstall(&$self) {
  $Caller = &TXMLRPC::Instance();
  $Caller->Add('pingback.ping', 'ping', get_class($self));
}

?>