<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class tevents extends tdata {
  protected $events;
  protected $eventnames;
  protected $map;
  
  public function __construct() {
    $this->eventnames = array();
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
$this->addmap('events', array());
  }
  
  public function assignmap() {
    foreach ($this->map as $propname => $key) {
      $this->$propname = &$this->data[$key];
    }
  }
  
  public function afterload() {
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
    if (in_array($name, $this->eventnames)) {
      $this->eventsubscribe($name, $value);
      return true;
    }
    return false;
  }
  
  public  function __call($name, $params) {
    if (in_array($name, $this->eventnames)) return $this->callevent($name, $params);
    parent::__call($name, $params);
  }
  
  protected function addevents() {
    $a = func_get_args();
    array_splice($this->eventnames, count($this->eventnames), 0, $a);
  }
  
  private function getevents($name) {
    if (isset($this->events[$name])) return $this->events[$name];
    return false;
  }
  
  private function callevent($name, &$params) {
    $result = '';
    if (    $list = $this->getevents($name)) {
      foreach ($list as $i => $item) {
        if (empty($item['class'])) {
          if (function_exists($item['func'])) {
            $call = $item['func'];
          } else {
            $this->eventdelete($name, $i);
            continue;
          }
        } elseif (!class_exists($item['class'])) {
          $this->eventdelete();
          continue;
        } else {
          $obj = getinstance($item['class']);
          $call = array(&$obj, $item['func']);
        }
$result = call_user_func_array($call, $params);
      }
    }
    
    return $result;
  }
  
  private function eventdelete($name, $i) {
      array_splice($this->events[$name], $i, 1);
$this->save();
    }

  public function eventsubscribe($name, $params) {
if (!in_array($name, $this->eventnames)) return $this->error("No such $name event");
    if (!isset($this->events[$name])) $this->events[$name] =array();
$list = $this->getevents($name);
    foreach ($list  as $event) {
      if (($event['class'] == $params['class']) && ($event['func'] == $params['func'])) return;
    }

    $this->events[$name][] = array(
'class' => $params['class'],
 'func' => $params['func']
);
      $this->save();
  }
  
  public function eventunsubscribe($name, $class) {
    if (    $list = $this->getevents($name)) {
      foreach ($list  as $i => $item) {
        if ($item['class'] == $class) {
          $this->eventdelete($name, $i);
          return true;
        }
      }
}
    return false;
  }
  
  public static function unsub(&$obj) {
    $self = self::instance();
    $self->unsubscribeclassname(get_class($obj));
}

  public function unsubscribeclass($obj) {
    $this->unsubscribeclassname(get_class($obj));
  }
  
  public function unsubscribeclassname($class) {
    foreach ($this->events as $name => $events) {
      foreach ($events as $i => $item) {
        if ($item['class'] == $class) array_splice($this->events[$name], $i, 1);
      }
    }

$this->save();
}
  
}//class

?>