<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class TXMLRPCComments extends TXMLRPCAbstract {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function delete($login, $password, $id, $idpost) {
$this->auth($login, $password, 'moderator');
$manager = tcommentmanager::instance();
return $manager->delete($id, $idpost);
}

  public function setstatus($login, $password, $id, $idpost, $status) {
$this->auth($login, $password, 'moderator');
$manager = tcommentmanager::instance();
return $manager->setstatus($id, $idpost, $status);
}

  public function add($login, $password, $idpost, $name, $email, $url, $content) {
$this->auth($login, $password, 'moderator');
$manager = tcommentmanager::instance();
return $manager->add($idpost, $name, $email, $url, $content);
}


  public function getcomment($login, $password, $id, $idpost) {
$this->auth($login, $password, 'moderator');
$comments = tcomments::instance($idpost);
$comment = $comments->getcomment($id);
return $comment->data;
}

  public function getrecent($login, $password, $count) {
$this->auth($login, $password, 'moderator');
$manager = tcommentmanager::instance();
return $manager->getrecent($count);
}

public function moderate($login, $password, $idpost, $list, $action) {
$this->auth($login, $password, 'moderator');
$manager = tcommentmanager::instance();
$delete = $action == 'delete';
foreach ($list as $id) {
if ($delete) {
$manager->delete($id, $idpost);
} else {
$manager->setstatus($id, $idpost, $action);
}
}
}
return true;
}

}//class
?>