<?php

class TEventClass extends TDataClass {
  protected $events;
  protected $EventNames;
  protected $DataMap;
  
  public function __construct() {
    $this->EventNames = array();
    $this->DataMap = array();
    parent::__construct();
    $this->AssignDataMap();
    $this->Load();
  }
  
  protected function CreateData() {
    $this->AddDataMap('events', array());
  }
  
  public function AssignDataMap() {
    foreach ($this->DataMap as $propname => $key) {
      $this->$propname = &$this->Data[$key];
    }
  }
  
  public function AfterLoad() {
    $this->AssignDataMap();
  }
  
  protected function AddDataMap($name, $value) {
    $this->DataMap[$name] = $name;
    $this->Data[$name] = $value;
    $this->$name = &$this->Data[$name];
  }
  
  public function __get($name) {
    if (method_exists($this, $name)) {
      return array(
      'class' =>get_class($this),
      'func' => $name
      );
    }
    
    return parent::__get($name);
  }
  
  public function __set($name, $value) {
    if (parent::__set($name, $value)) return true;
    if ($this->SetEvent($name, $value)) return true;
    $this->Error("Unknown property $name in class ". get_class($this));
  }
  
  protected function SetEvent($name, $value) {
    if (in_array($name, $this->EventNames)) {
      $this->SubscribeEvent($name, $value);
      return true;
    }
    return false;
  }
  
  public  function __call($name, $params) {
    if (in_array($name, $this->EventNames)) {
      return $this->CallEvent($name, $params);
    }
    
    parent::__call($name, $params);
  }
  
  protected function AddEvents() {
    $a = func_get_args();
    array_splice($this->EventNames, count($this->EventNames), 0, $a);
  }
  
  private function CallEvent($name, &$params) {
    if (!isset($this->events[$name])) return '';
    $Result = '';
    $list = &$this->events[$name];
    for($i = count($list) -1; $i >= 0; $i--) {
      $function = $list[$i]['func'];
      $classname = $list[$i]['class'];
      if (empty($classname)) {
        if (function_exists($function)) {
          $lResult = call_user_func_array($function, $params);
          if (is_string($lResult)) $Result .= $lResult;
        } else {
          array_splice($list, $i, 1);
          $this->Save();
        }
      } else {
        
        if (!@class_exists($classname)) {
          __autoload($classname);
          if (!@class_exists($classname)) {
            array_splice($list, $i, 1);
            $this->Save();
            continue;
          }
        }
        
        $obj = &GetInstance($classname);
        $lResult = call_user_func_array(array(&$obj, $function), $params);
        if (is_string($lResult)) $Result .= $lResult;
      }
    }
    
    return $Result;
  }
  
  public function SubscribeEvent($name, $params) {
    if (!isset($this->events[$name])) {
      $this->events[$name] =array();
    }
    
    foreach ($this->events[$name] as $event) {
      if (($event['class'] == $params['class']) && ($event['func'] == $params['func'])) return;
    }
    
    $this->events[$name][] = array(
    'class' => $params['class'],
    'func' => $params['func']
    );
    $this->Save();
  }
  
  public function UnsubscribeEvent($EventName, $ClassName) {
    if (isset($this->events[$EventName])) {
      $lEvents = &$this->events[$EventName];
      for ($i = count($lEvents) - 1; $i >=  0; $i--) {
        if ($lEvents[$i]['class'] == $ClassName) {
          array_splice($lEvents, $i, 1);
          $this->Save();
          return true;
        }
      }
    }
    return false;
  }
  
  public static function unsub(&$obj) {
    $self = self::Instance();
    $self->UnsubscribeClassName(get_class($obj));
  }
  
  public function UnsubscribeClass(&$obj) {
    $this->UnsubscribeClassName(get_class($obj));
  }
  
  public function UnsubscribeClassName($class) {
    $this->Lock();
    foreach ($this->events as $name => $events) {
      for ($i = count($events) - 1; $i >=  0; $i--) {
        if ($events[$i]['class'] == $class) {
          array_splice($this->events[$name], $i, 1);
        }
      }
    }
    $this->Unlock();
  }
  
  public function Validate() {
    foreach ($this->EventNames as $name) {
      if (Method_exists($this, $name)) $this->Error("the virtual method $name cannt be exist in class". get_class($this));
    }
  }
  
}

?>