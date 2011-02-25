<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadmincommoncomments extends tadminmenu {
  protected $user;
  
  protected function getmanager() {
    return litepublisher::$classes->commentmanager;
  }

public function buildtable() {
$table = new ttablecolumns();

}
  
}//class