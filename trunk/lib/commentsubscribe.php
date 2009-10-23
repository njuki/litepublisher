<?php

class tsubscribers extends TItems {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
$this->table = 'subscribers';
    $this->basename = 'subscribers';
    $this->Data['fromemail'] = '';
    $this->Data['enabled'] = true;
    $this->Data['locklist'] = '';
  }

public function add($pid, $uid) {
if (dbversion) {
$this->db->InsertAssoc(array(
'post' => $pid,
'author' => $uid
));
} else {
if (!isset($this->items[$pid]))  $this->items[$pid] = array();
if (!in_array($uid, $this->items[$pid])) {
$this->items[$pid][] =$uid;
$this->save();
return true;
}
return false;
}
}

public function delete($pid, $uid) {
if (dbversion) {
return $this->db->delete("post = $pid and $author = $uid");
} elseif (isset($this->items[$pid])) {
    $i = array_search($uid, $this->items[$pid]);
    if (is_int($i))  {
array_splice($this->items[$pid], $i, 1);
$this->save();
return true;
}
return false;
}
}

public function deletepost($pid) {
if (dbversion) {
$this->db->delete("post = $pid");
} elseif (isset($this->items[$pid])) {
unset($this->items[$pid]);
$this->save();
} else {
}

public function deleteauthor($uid) {
if (dbversion) {
$this->db->delete("author = $uid");
} else {
foreach ($this->items as $pid => $item) {
    $i = array_search($uid, $item);
    if (is_int($i))  array_splice($this->items[$pid], $i, 1);
    }
$this->save();
}
}

  public function update($pid, $uid, $subscribed) {
if (dbversion) {
$this->delete($pid, $uid);
if ($subscribed) $this->add($pid, $uid);
} elseif ($subscribed) {
if (!isset($this->items[$pid]))  $this->items[$pid] = array();
if (!in_array($uid, $this->items[$pid])) {
$this->items[$pid][] =$uid;
$this->save();
}
} else {
$this->delete($pid, $uid);
}
  }
  
   public function setenabled($value) {
global $classes;
    if ($this->enabled != $value) {
      $this->Data['enabled'] = $value;
      $this->save();
      $manager = $classes->commentmanager;
      if ($value) {
        $manager->lock();
        $manager->added = $this->sendmail;
        $manager->approved = $this->sendmail;
        $manager->unlock();
      } else {
        $manager->UnsubscribeClass($this);
      }
    }
  }

public function getitems($uid) {
if (dbversion) {
return $this->db->res2array($this->db->query("select post from $this->thistable where author = $uid"));
} else {
$result = array();
foreach ($this->items as $pid => $items) {
if (in_array($uid, $items)) $result[] = $pid;
}
return $result;
}
}
  
  public function geturl() {
    global $options;
    return $options->url . '/admin/subscribe/' . $options->q;
  }
  
  public function sendmail($id) {
global $classes;
    if (!$this->enabled) return;
    
    $manager = $classes->commentmanager;
    $item = $manager->getitem($id);
if (dbversion) {
if ($item['status'] != 'approved') || ($item['pingback'] == '1')) return;
} else {
    if (isset($item['status']) || isset($item['type']))return;
}
    
    $cron = tcron::instance();
    $cron->add('single', get_class($this),  'cronsendmail', $id);
  }
  
  public function cronsendmail($id) {
    global $options, $classes;
    $manager = $classes->commentmanager;
    if (!$manager->itemexists($id)) return;
    $item = $manager->getitem($id);
$pid = $item['pid'];
if (dbversion) {
if ($this->db->getcount("post = $pid") == 0) return;
} else {
if (!isset($this->items[$pid]) || (count($this->items[$pid]) == 0)) return;
}
   
    $comment = $manager->getcomment($id);

    $html = THtmlResource::instance();
    $html->section = 'moderator';
    $lang = tlocal::instance();
    
    eval('$subj = "'. $html->subject . '";');
    eval('$body = "' . $html->subscriberbody . '";');
    
    $url = $this->Geturl();
    
    $users = tcomusers::instance();
    foreach ($subscribers as $uid) {
      $user = $comusers->getitem($uid);
      if (empty($user['email'])) continue;
      if (strpos($this->locklist, $user['email']) !== false) continue;
  $link = "\n{$url}userid={$user['cookie']}\n";
      tmailer::sendmail($options->name, $this->fromemail,  $user['name'], $user['email'],
      $subj, $body . $link);
    }
  }
  
}//class

?>