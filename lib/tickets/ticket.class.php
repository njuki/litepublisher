<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tticket extends titem {
public $post;

  public static function instance($id = 0) {
    return parent::instance(__class__, $id);
  }

  public function getbasename() {
    return 'posts' . DIRECTORY_SEPARATOR . $this->post->id  . DIRECTORY_SEPARATOR . 'ticket';
  }
  
  protected function create() {
    $this->table = 'tickets';
    $this->data= array(
'id' => 0,
'type' => 'bug',
'state'  => 'opened',
'prio' => 'major',
'assignto' => 0,
'closed' = '',
'version'=> litepublisher::$options->version,
'votes' => 0,
'os'=> '*',
'reproduced' => false,
'reproduce_code' => ''
);    
  }
  
  public function getdbversion() {
    return dbversion;
  }
  
  //db
  public function load() {
$result = dbversion? $this->LoadFromDB() : parent::load();
if ($result) {
      self::$instances[get_class($this)][$this->post->id] = $this;
    }
return $result;
  }
  
  protected function LoadFromDB() {
    if ($a = $this->db->getitem($this->post->id)) {
$this->data = $a;
      return true;
    }
    return false;
  }
  
  protected function SaveToDB() {
$this->db->updateassoc($this->data);
}



}//class
?>