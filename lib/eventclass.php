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
    $this->load();
  }
  
  public function free() {
    global $classes;
    unset($classes->instances[get_class($this)]);
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
  

private function GetEvents($name) {
return isset($this->events[$name])?$this->events[$name] : false;
}

  private function CallEvent($name, &$params) {
    $Result = '';
if (    $list = $this->GetEvents($name)) {
foreach ($list as $i => $item) {
      if (empty($item['class'])) {
        if (function_exists($item['func'])) {
$call = $item['func'];
} else {
$this->DeleteEvent($name, $i);
continue;
}
        } elseif (!class_exists($item['class'])) {
$this->DeleteEvent();
          continue;
        } else {
                $obj = &GetInstance($item['class']);
$call = array(&$obj, $item['func']);
}
        $lResult = call_user_func_array($call, $params);
        if (is_string($lResult)) $Result .= $lResult;
    }
}
    
    return $Result;
  }

private function DeleteEvent($name, $i) {
          array_splice($this->events[$name], $i, 1);
          $this->save();
}
  
  public function SubscribeEvent($name, $params) {
    if (!isset($this->events[$name])) $this->events[$name] =array();
   foreach ($this->events[$name] as $event) {
      if (($event['class'] == $params['class']) && ($event['func'] == $params['func'])) return;
    }
    
    $this->events[$name][] = array(
    'class' => $params['class'],
    'func' => $params['func']
    );
    $this->save();
  }
  
  public function UnsubscribeEvent($name, $class) {
    if (isset($this->events[$name])) {
foreach ($this->events[$name] as $i => $item) {
        if ($item['class'] == $class) {
$this->DeleteEvent($name, $i);
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
    $this->lock();
    foreach ($this->events as $name => $events) {
foreach ($events as $i => $item) {
        if ($item['class'] == $class)  $this->DeleteEvent($name, $i);
        }
    }
    $this->unlock();
  }
  
  public function Validate() {
    foreach ($this->EventNames as $name) {
      if (Method_exists($this, $name)) $this->Error("the virtual method $name cannt be exist in class". get_class($this));
    }
  }
  
}

?>