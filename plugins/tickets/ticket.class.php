<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tticket extends titem {
  public $post;
  private $selfexists; // flagto add
  
  public static function instance($id = 0) {
    return parent::instance(__class__, $id);
  }
  
  public function getbasename() {
    return 'posts' . DIRECTORY_SEPARATOR . $this->post->id  . DIRECTORY_SEPARATOR . 'ticket';
  }
  
  protected function create() {
    $this->table = 'tickets';
    $this->selfexists = false;
    $this->data= array(
    'id' => 0,
    'type' => 'bug',
    'state'  => 'opened',
    'prio' => 'major',
    'assignto' => 0,
    'closed' => '',
    'version'=> litepublisher::$options->version,
    'votes' => 0,
    'poll' => 0,
    'os'=> '*',
    'reproduced' => false,
    'code' => ''
    );
  }
  
  public function getdbversion() {
    return dbversion;
  }
  
  //db
  public function load() {
    if ($this->post->id == 0) return false;
    $result = dbversion? $this->LoadFromDB() : parent::load();
    if ($result) {
      $this->selfexists = true;
      self::$instances[get_class($this)][$this->post->id] = $this;
    }
    return $result;
  }
  
  protected function LoadFromDB() {
    if ($a = $this->db->getitem($this->post->id)) {
      $this->data = $a;
      $this->data['reproduced'] = $a['reproduced'] == '1';
      return true;
    }
    return false;
  }
  
  protected function SaveToDB() {
    if ($this->data['closed'] == '') $this->data['closed'] = sqldate();
    $this->data['id'] = $this->post->id;
    if ($this->selfexists) {
      $this->db->updateassoc($this->data);
    } else {
      $this->db->add($this->data);
    }
  }
  
  protected function getticket() {
    return $this;
  }
  
  protected function getclosed() {
    return strtotime($this->data['closed']);
  }
  
  protected function setclosed($value) {
    $this->data['closed'] = sqldate($value);
  }
  
}//class
?>