<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

function TXMLRPCLivejournalInstall(&$self) {
  $Caller = &TXMLRPC::Instance();
  $Caller->Lock();
  
  //Live journal api
  $Caller->Add('LJ.XMLRPC.login' , 'login', get_class($self));
  $Caller->Add('LJ.XMLRPC.getchallenge', 'getchallenge', get_class($self));
  $Caller->Add('LJ.XMLRPC.editevent', 'editevent', get_class($self));
  $Caller->Add('LJ.XMLRPC.postevent', 'postevent', get_class($self));
  //$Caller->Add('LJ.XMLRPC.checkfriends', 'checkfriends', get_class($self));
  $Caller->Unlock();
}

?>