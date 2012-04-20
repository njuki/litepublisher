<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

    class tkeptcomments extends tdata {
      
      public static function i() {
        return getinstance(__class__);
      }
      
      protected function create() {
        parent::create();
        $this->table ='commentskept';
        
      }
      
      public function deleteold() {
        $this->db->delete(sprintf("posted < '%s' - INTERVAL 20 minute ", sqldate()));
      }
      
      public function add($values) {
        $confirmid = md5uniq();
        $this->db->add(array(
        'id' => $confirmid,
        'posted' => sqldate(),
        'vals' => serialize($values)
        ));
        return $confirmid;
      }
      
      public function getitem($confirmid) {
        if ($item = $this->db->getitem(dbquote($confirmid))) {
          return unserialize($item['vals']);
        }
        return false;
      }
      
    }//class