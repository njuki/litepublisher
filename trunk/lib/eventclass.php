<?php

class TEventClass extends TDataClass {
  protected $events;
  protected $EventNames;
  protected $map;
  
  public function __construct() {
    $this->EventNames = array();
    $this->map = array();
    parent::__construct();
    $this->assignmap();
    $this->load();
  }
  
  public function free() {
    global $classes;
    unset($classes->instances[get_class($this)]);
  }
  
  protected function create() {
    if (!dbversion) $this->addmap('events', array());
  }
  
  public function assignmap() {
    foreach ($this->map as $propname => $key) {
      $this->$propname = &$this->data[$key];
    }
  }
  
  public function AfterLoad() {
    $this->assignmap();
  }
  
  protected function addmap($name, $value) {
    $this->map[$name] = $name;
    $this->data[$name] = $value;
    $this->$name = &$this->data[$name];
  }
  
  public function __get($name) {
    if (method_exists($this, $name)) return array('class' =>get_class($this), 'func' => $name);
    return parent::__get($name);
  }
  
  public function __set($name, $value) {
    if (parent::__set($name, $value)) return true;
    if ($this->setevent($name, $value)) return true;
    $this->error("Unknown property $name in class ". get_class($this));
  }
  
  protected function setevent($name, $value) {
    if (in_array($name, $this->EventNames)) {
      $this->SubscribeEvent($name, $value);
      return true;
    }
    return false;
  }
  
  public  function __call($name, $params) {
    if (in_array($name, $this->EventNames)) return $this->CallEvent($name, $params);
    parent::__call($name, $params);
  }
  
  protected function addevents() {
    $a = func_get_args();
    array_splice($this->EventNames, count($this->EventNames), 0, $a);
  }
  
  private function getevents($name) {
    if (isset($this->events[$name])) return $this->events[$name];
    if (dbversion) {
      if ($res = $this->getdb('events')->select("owner = '$this->class' and name = '$name'")) {
      $this->events[$name] = $res->fetchAll (PDO::FETCH_ASSOC);
      return $this->events[$name];
}
    }
    return false;
  }
  
  private function CallEvent($name, &$params) {
    $result = '';
    if (    $list = $this->getevents($name)) {
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
          $obj = getinstance($item['class']);
          $call = array(&$obj, $item['func']);
        }
        $lresult = call_user_func_array($call, $params);
        if (is_string($lresult)) $result .= $lresult;
      }
    }
    
    return $result;
  }
  
  private function DeleteEvent($name, $i) {
    if (dbversion) {
      $id =           $this->events[$name][$i]['id'];
      $this->getdb('events')->iddelete($id);
      array_splice($this->events[$name], $i, 1);
    } else {
      array_splice($this->events[$name], $i, 1);
      $this->save();
    }
  }
  
  public function SubscribeEvent($name, $params) {
    if (!isset($this->events[$name])) $this->events[$name] =array();
    foreach ($this->events[$name] as $event) {
      if (($event['class'] == $params['class']) && ($event['func'] == $params['func'])) return;
    }
    
    $this->events[$name][] = array('class' => $params['class'], 'func' => $params['func']);
    if (dbversion) {
      $event = &$this->events[$name][count($this->events[$name]) - 1];
      $event['name'] = $name;
      $event['owner'] = get_class($this);
      $event['id'] = $this->getdb('events')->add($event);
    } else {
      $this->save();
    }
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
    $self = self::instance();
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
  

public function getoptions() {
global $options;
if (!isset($options->data[$this->basename])) $options->data[$this->basename] = array();
return new tarray2prop($options->data[$this->basename]);
}

public function setoptions($values) {
global $options;
$options->__set($this->basename, $values);
}

}//class

?>