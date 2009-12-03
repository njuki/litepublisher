<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class tspamfilter extends tevents {

  public static function instance() {
    return getinstance(__class__);
  }

protected function create() {
parent::create();
$this->basename = 'spamfilter';
}

  public function createstatus($authorid, $content) {
    global $options;
    if ($options->DefaultCommentStatus == 'approved') return 'approved';
$comusers = tcomusers::instance();
    if ($comusers->trusted($authorid)) return  'approved';
    return 'hold';
  }
  
   public function trusted($authorid) {
$comusers = tcomusers::instance();
return
global $classes;
$manager = $classes->commentmanager;
if (dbversion) {
if (($res = $manager->db->select("author = $authorid and status = 'approved' limit 1")) && $res->fetch()) return true;
} else {
    foreach ($manager->items as $id => $item) {
      if (($authorid == $item['uid']) && !isset($item['status'])) return true;
    }
}
return false;
}

public function HasApprovedCount($userid, $count) {
global $classes;
$manager= $classes->commentmanager;
if (dbversion) {
if ($approved = $manager->db->getcount("author = $userid and status = 'approved' limit $count")){
return $approved >= $count;
}
return false;
} else {
    foreach ($manager->items as $id => $item) {
      if (($userid == $item['uid']) && !isset($item['status'])) {
        if (--$count ==0) return true;
      }
    }
    return false;
}
}

public function UserCanAdd($userid) {
global $classes;
$manager= $classes->commentmanager;
if (dbversion) {
$res = $manager->db->query("select count(id) as count from $manager->thistable where author = $userid
union select count(id) as approved from $manager->thistable where author = $userid and status = 'approved'");
extract($res->fetch());
  } else {
    $count = 0;
    $approved = 0;
    foreach($manager->items as $id => $item) {
      if ($item['uid'] == $userid) {
        $count++;
        if (!isset($item['status']) ) $approved++;
      }
    }
  }
    if ($count < 2) return true;
    if  ($approved ==0) return false;
    return true;
}


public function checkduplicate($idpost, $content) {
global $classes;
$content = trim($content);
if (dbversion) {
global $db;
$db->table = $classes->commentmanager->rawtable;
$hash = md5($content);
return $db->findid("hash = '$hash'";
 } else {
$comments = tcomments(/$postid);
    return $comments->IndexOf('rawcontent', $content) > 0;
}
}

}//class
?>