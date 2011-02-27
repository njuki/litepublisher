<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminusers extends tadminmenu {
  
  public static function instance($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function getcontent() {
    $result = '';
    $lang = $this->lang;
    $users = tusers::instance();
    $groups = tusergroups::instance();
    $a = array();
    foreach ($groups->items as $id => $item) {
    $a[$id] = $lang->{$item['name']};
    }
    
    $statuses = array();
    foreach (array('approved', 'hold', 'lock', 'wait')as $name) {
      $statuses[$name] = $lang->$name;
    }
    
    $html = $this->html;
    $args = targs::instance();
    $id = $this->idget();
    if ($users->itemexists($id)) {
      $item = $users->getitem($id);
      $args->add($item);
      if (isset($_GET['action']) &&($_GET['action'] == 'delete'))  {
        if  ($this->confirmed) {
          $users->delete($id);
          $result .= $html->h2->successdeleted;
        } else {
          $args->id = $id;
          $args->adminurl = $this->adminurl;
          $args->action = 'delete';
          $args->confirm = $this->lang->confirmdelete;
          $result .=$html->confirmform($args);
        }
      } else {
        $args->groupcombo = $html->array2combo($a, $item['gid']);
        $args->statuscombo = $html->array2combo($statuses, $item['status']);
        $result .= $html->form($args);
      }
      
    } else {
      $item = array(
      'login' => '',
      'password' => '',
      'cookie' =>  '',
      'expired' => sqldate(),
      'registered' => sqldate(),
      'gid' => 'nobody',
      'trust' => 0,
      'status' => 'hold',
      'name' => '',
      'email' => '',
      'url' => '',
      'ip' => '',
      'avatar' => 0
      );
      $args->groupcombo = $html->array2combo($a, $item['gid']);
      $args->statuscombo = $html->array2combo($statuses, $item['status']);
      $args->add($item);
      $result .= $html->form($args);
    }
    
    //table
    $perpage = 20;
    $count = $users->count;
    $from = $this->getfrom($perpage, $count);
    if ($users->dbversion) {
      $items = $users->select('', " order by registered desc limit $from, $perpage");
      if (!$items) $items = array();
    } else {
      $items = array_slice(array_keys($users->items), $from, $perpage);
    }
    
    $args->adminurl = $this->adminurl;
    $result .= $html->tableheader ();
    foreach ($items as $id) {
      $item = $users->getitem($id);
      $args->add($item);
      $args->id = $id;
      $args->group = $a[$item['gid']];
      $args->status = $statuses[$item['status']];
      $result .= $html->item($args);
    }
    $result .= $html->tablefooter();
    $result = $html->fixquote($result);
    
    $theme = ttheme::instance();
    $result .= $theme->getpages($this->url, litepublisher::$urlmap->page, ceil($count/$perpage));
    return $result;
  }
  
  public function processform() {
    $users = tusers::instance();
    
    if (isset($_POST['table'])) {
      $users->lock();
      foreach ($_POST as $key => $value) {
        if (!is_numeric($value)) continue;
        $id = (int) $value;
        $users->delete($id);
      }
      $users->unlock();
      return $this->html->h2->successdeleted;
    }
    
    $id = $this->idget();
    if ($id == 0) {
      extract($_POST, EXTR_SKIP);
      $id = $users->add($group, $login,$password, $name, $email, $url);
      if (!$id) return $this->html->h2->invalidregdata;
    } else {
      if (!$users->edit($id, $_POST))return $this->notfound;
    }
  }
  
}//class


?>