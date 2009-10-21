<?php

class tspamfilter extends TEventClass {

protected function create() {
parent::create();
$this->basename = 'spamfilter';
}

public function AuthorHasApproved($authorid) {
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

public function HasApprovedCount($userid, $count);
global $classes;
$manager= $classes->commentmanager;
if (dbversion) {
if (($res = $manager->db->query(select count(author) as count from $manager->thistable where author = $userid and status = 'approved' limit $count")) && ($row = $res->fetch()) return $count <= $row['count'];
} else {
    foreach ($manger->items as $id => $item) {
      if (($userid == $item['uid']) && !isset($item['status'])) {
        if (--$count ==0) return true;
      }
    }
    return false;
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

}//class
?>