<?php
require_once(dirname(__file__) . DIRECTORY_SEPARATOR  . 'xmlrpc-abstractclass.php');

class TXMLRPCRemoteAdmin extends TXMLRPCAbstract {
 
 public static function &Instance() {
  return GetInstance(__class__);
 }
 
 public function Install() {
  $Caller = &TXMLRPC::Instance();
  $Caller->Lock();
  $Caller->Add('blogolet.call', 'BlogoletCall', get_class($this));
  $Caller->Add('blogolet.set', 'BlogoletSet', get_class($this));
  $Caller->Add('blogolet.get', 'BlogoletGet', get_class($this));
  
  $Caller->Unlock();
 }
 
 public function BlogoletCall(&$args) {
  if (!$this->CanLogin($args, 0)) {
   return $this->Error;
  }
  
  $class = $args[2];
  $function = $args[3];
  $params = &$args[4];
  
  if ( empty($function))  {
   return new IXR_Error(-32601, "server error. requested function name is  empty");
  }
  
  if (empty($class)) {
   if (is_array($params)) {
    return call_user_func($function, $params);
   } else {
    return $function($params);
   }
  }
  
  if (!class_exists($class)) {
   __autoload($class);
   if (!class_exists($class)) {
    return new IXR_Error(-32601, "server error. requested class \"$class\" does not exist.");
   }
  }
  
  $obj = &GetInstance($class);
  
  if (is_array($params)) {
   return call_user_func_array(array(&$obj, $function), $params);
  } else {
   return $obj->$function($params);
  }
 }
 
 public function BlogoletGet(&$args) {
  if (!$this->CanLogin($args, 0)) {
   return $this->Error;
  }
  
  $class = $args[2];
  $propname = $args[3];
  
  if (empty($class) || empty($propname))  {
   return new IXR_Error(-32601, "server error. requested class  or property name is empty.");
  }
  
  if (!class_exists($class)) {
   __autoload($class);
   if (!class_exists($class)) {
    return new IXR_Error(-32601, "server error. requested class \"$class\" does not exist.");
   }
  }
  
  $obj = &GetInstance($class);
  return $obj->$propname;
 }
 
 public function BlogoletSet(&$args) {
  if (!$this->CanLogin($args, 0)) {
   return $this->Error;
  }
  
  $class = $args[2];
  $propname = $args[3];
  $value = &$args[4];
  
  if (empty($class) || empty($propname))  {
   return new IXR_Error(-32601, "server error. requested class  or property name is empty.");
  }
  
  if (!class_exists($class)) {
   __autoload($class);
   if (!class_exists($class)) {
    return new IXR_Error(-32601, "server error. requested class \"$class\" does not exist.");
   }
  }
  
  $obj = &GetInstance($class);
  $obj->$propname = $value;
  return true;
 }
 
}//class


?>