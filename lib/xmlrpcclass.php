<?php
require_once(dirname(__file__) . DIRECTORY_SEPARATOR  . 'include'. DIRECTORY_SEPARATOR  . 'class-IXR.php');

class TXMLRPCParser extends IXR_Server  {
 public $XMLResult;
 private $Owner;
 
 public function __construct(&$AOwner) {
  $this->Owner = &$AOwner;
 }
 
 function call($methodname, $args) {
  return $this->Owner->Call($methodname, $args);
 }
 
 function output($xml) {
  global $Options;
  $head = '<?xml version="1.0"?>' . "\n";
  $length = strlen($xml) + strlen($head);
  $this->XMLResult = "<?php
  @header('Connection: close');
  @header('Content-Length: $length');
  @header('Content-Type: text/xml');
  @header('Date: ".date('r') . "');
  @header('X-Pingback: $Options->pingurl');
  echo'$head';
  ?>". $xml;
 }
 
}//class

class TXMLRPC extends TEventClass {
 public $Server;
 protected $methods;
 
 public static function &Instance() {
  return GetInstance(__class__);
 }
 
 protected function CreateData() {
  parent::CreateData();
  $this->basename = 'xmlrpc';
  $this->CacheEnabled = false;
  $this->AddEvents('BeforeCall', 'AfterCall', 'GetMethods');
  $this->AddDataMap('methods', array());
 }
 
 public function Request($param) {
  global$HTTP_RAW_POST_DATA;
  $_COOKIE = array();
  if ( !isset( $HTTP_RAW_POST_DATA ) ) {
   $HTTP_RAW_POST_DATA = file_get_contents( 'php://input' );
  }
  if ( isset($HTTP_RAW_POST_DATA) ) {
   $HTTP_RAW_POST_DATA = trim($HTTP_RAW_POST_DATA);
  }
  
  if (defined('debug')) TFiler::log("request:\n" . $HTTP_RAW_POST_DATA);
  //$HTTP_RAW_POST_DATA = file_get_contents('raw.txt');
  
  $this->GetMethods();
  $this->Server =&new TXMLRPCParser ($this);
  $this->Server->IXR_Server  ($this->methods);
  $Result = $this->Server->XMLResult;
  $this->AfterCall();
  if (defined('debug')) TFiler::log("responnse:\n".$Result);
  return $Result;
 }
 
 public function Call($methodname, &$args) {
  $this->BeforeCall($methodname, $args);
  if (!isset($this->methods[$methodname])) {
   return new IXR_Error(-32601, 'server error. requested method '.$methodname.' does not exist.');
  }
  
  if (count($args) == 1) {
   $args = $args[0];
  }
  
  $ClassName = $this->methods[$methodname]['class'];
  $Function= $this->methods[$methodname]['func'];
  
  if (empty($ClassName)) {
   if (function_exists($Function)) {
    return call_user_func($Function, $args);
   } else {
    $this->Delete($methodname);
    return new IXR_Error(-32601, "server error. requested function \"$Function\" does not exist.");
   }
   
  } else {
   //create class instance
   if (!class_exists($ClassName)) {
    __autoload($ClassName);
    if (!class_exists($ClassName)) {
     $this->Delete($methodname);
     return new IXR_Error(-32601, "server error. requested class \"$ClassName\" does not exist.");
    }
   }
   
   $Obj = &GetInstance($ClassName);
   
   if (!method_exists($Obj, $Function)) {
    $this->Delete($methodname);
    return new IXR_Error(-32601, "server error. requested object method \"$Function\" does not exist.");
   }
   
   return $Obj->$Function($args);
  }
  
 }
 
 public function  Add($method, $Function, $ClassName) {
  $this->methods[$method] = array(
  'class' => $ClassName,
  'func' => $Function
  );
  $this->Save();
 }
 
 public function Delete($method) {
  if (isset($this->methods[$method])) {
   unset($this->methods[$method]);
   $this->Save();
  }
 }
 
 public function RemoveClass($ClassName) {
  foreach ($this->methods as $method => $Item) {
   if ($ClassName == $Item['class']) {
    unset($this->methods[$method]);
   }
  }
  $this->Save();
 }
 
 public function sayHello($args) {
  return 'Hello!';
 }
 
 public function addTwoNumbers($args) {
  $number1 = $args[0];
  $number2 = $args[1];
  return $number1 + $number2;
 }
 
}

?>