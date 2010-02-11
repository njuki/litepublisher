<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class TXMLRPCOpenAction extends TItems {
  public $actions;
  public $from;
  
  public static function &Instance() {
    return GetInstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'openaction';
    $this->addmap('actions', array());
  }
  
  public function send(&$action) {
    if (!$this->DoConfirm($action)) {
      return new IXR_Error(403, 'Action not confirmed');
    }
    
    if (!isset($this->items[$action['name']])) {
    return new IXR_Error(404, "The {$action['name']} action not registered");
    }
    
    $this->from = $action['server'];
    return $this->DoAction($action['name'], $action['arg']);
  }
  
  private function DoConfirm(&$action) {
    $Client  = new IXR_Client($action['server']);
    if ($Client->query('openaction.confirm', $action)) {
      return $Client->getResponse();
    }
    return false;
  }
  
  public function confirm(&$action) {
    $this->DeleteExpired();
    extract($action);
    if (!$this->HasAction($id)) {
      return new IXR_Error(403, 'Action not found');
    }
    
    if ($server != litepublisher::$options->url . '/rpc.xml') {
      return new IXR_Error(403, 'Bad xmlrpc server');
    }
    
    return true;
  }
  
  private function HasAction($id) {
    return isset($this->actions[$id]);
  }
  
  private function DoAction($name, $arg) {
    $class = $this->items[$name]['class'];
    $func = $this->items[$name]['func'];
    if (empty($class)) {
      if (function_exists($func)) {
        return $func($arg);
      } else {
        unset($this->items[$name]);$this->Save();
        return new IXR_Error(404, 'The requested function not found');
      }
    } else {
      if (@class_exists($class)) {
        $obj = GetInstance($class);
        return $obj->$func($arg);
      } else {
        unset($this->items[$name]);
        $this->Save();
        return new IXR_Error(404, 'The requested class not found');
      }
    }
  }
  
  public function CallAction($to, $name, $arg) {
    $this->Lock();
    $this->DeleteExpired();
    $id = md5uniq();
    $this->actions[$id] = array(
    'date' => time(),
    'to' => $to,
    'name' => $name,
    'arg' => $arg
    );
    $this->Unlock();
    
    $action =array(
    'id' => $id,
    'server' => litepublisher::$options->url . '/rpc.xml',
    'name' => $name,
    'arg' => $arg
    );
    
    $Client  = new IXR_Client($to);
    if ($Client->query('openaction.send', $action)) {
      return $Client->getResponse();
    }
    return false;
  }
  
  private function DeleteExpired() {
    $this->Lock();
    $expired = time() - litepublisher::$options->CacheExpired;
    foreach ($this->actions as $id => $item) {
      if ($item['date'] < $expired) unset($this->actions[$id]);
    }
    $this->Unlock();
  }
  
  public function Add($name, $class, $func) {
    $this->items[$name] = array(
    'class' => $class,
    'func' => $func
    );
    $this->Save();
  }
  
  public function DeleteClass($class) {
    foreach ($this->items as $id => $item) {
      if ($class == $item['class']) unset($this->items[$id]);
    }
    $tjhis->Save();
  }
  
}//class

?>