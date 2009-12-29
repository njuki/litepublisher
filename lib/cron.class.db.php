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
    global $options;
    if (ob_get_level()) ob_end_flush ();
    echo "<pre>\n";
    while ($item = $this->db->getassoc("date <= now() order by date asc limit 1")) {
sleep(2);
      extract($item);
  $this->log("task started:\n{$class}->{$func}($arg)");
      $arg = unserialize($arg);      
      if ($class == '' ) {
        if (function_exists($func)) {
          try {
            $func($arg);
          } catch (Exception $e) {
            $options->handexception($e);
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
          $options->handexception($e);
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
    
    return $id;
  }
  
  public function delete($id) {
    $this->db->iddelete($id);
  }
  
  public function deleteclass($class) {
    $this->db->delete("class = '$class'");
  }
  
}//class

?>