<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tcron extends tabstractcron {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->table = 'cron';
  }
  
  protected function execute() {
    if (ob_get_level()) ob_end_flush ();
    echo "<pre>\n";
    while ($item = $this->db->getassoc(sprintf("date <= '%s' order by date asc limit 1", sqldate()))) {
      sleep(2);
      extract($item);
  $this->log("task started:\n{$class}->{$func}($arg)");
      $arg = unserialize($arg);
      if ($class == '' ) {
        if (function_exists($func)) {
          try {
            $func($arg);
          } catch (Exception $e) {
            litepublisher::$options->handexception($e);
          }
        } else {
          $this->db->iddelete($id);
          continue;
        }
      } elseif (class_exists($class)) {
        try {
          $obj = getinstance($class);
          $obj->$func($arg);
        } catch (Exception $e) {
          litepublisher::$options->handexception($e);
        }
      } else {
        $this->db->iddelete($id);
        continue;
      }
      if ($type == 'single') {
        $this->db->iddelete($id);
      } else {
        $date = sqldate(strtotime("+1 $type"));
        $this->db->setvalue($id, 'date', $date);
      }
    }
  }
  
  protected function doadd($type, $class, $func, $arg ) {
    $id = $this->db->add(array(
    'date' => sqldate(),
    'type' => $type,
    'class' =>  $class,
    'func' => $func,
    'arg' => serialize($arg)
    ));
    
    $this->added($id);
    return $id;
  }
  
  public function addnightly($class, $func, $arg) {
    $id = $this->db->add(array(
    'date' => date('Y-m-d 03:15:00', time()),
    'type' => 'day',
    'class' =>  $class,
    'func' => $func,
    'arg' => serialize($arg)
    ));
    $this->added($id);
    return $id;
  }
  
  public function addweekly($class, $func, $arg) {
    $id = $this->db->add(array(
    'date' => date('Y-m-d 03:15:00', time()),
    'type' => 'week',
    'class' =>  $class,
    'func' => $func,
    'arg' => serialize($arg)
    ));
    
    $this->added($id);
    return $id;
  }
  
  public function delete($id) {
    $this->db->iddelete($id);
    $this->deleted($id);
  }
  
  public function deleteclass($class) {
    $this->db->delete("class = '$class'");
  }
  
}//class

?>