<?php

class TXMLRPCAbstract extends TEventClass {
 public $Error;
 
 public function GetBaseName() {
  return 'xmlrpc-abstract';
 }
 
 public function Uninstall() {
  $Caller = &TXMLRPC::Instance();
  $Caller->RemoveClass(get_class($this));
 }
 
 public function CanLogin(&$args, $LoginIndex = 1) {
  global $Options;
  if (!$Options->CheckLogin($args[$LoginIndex], $args[$LoginIndex + 1])) {
   $this->Error = new IXR_Error(403, 'Bad login/pass combination.');
   return false;
  }
  return true;
 }
 
}

?>