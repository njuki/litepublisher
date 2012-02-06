<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminusers extends tadminmenu {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function gethead() {
    $result = parent::gethead();
    $template = ttemplate::i();
  $result .= $template->getready('$("#tabs").tabs({ cache: true });');
    return $result;
  }
  
  public function getcontent() {
    $result = '';
    $users = tusers::i();
    $groups = tusergroups::i();
    
    $html = $this->html;
    $lang = tlocal::i('users');
    $args = targs::i();
    
    $a = array();
    foreach ($groups->items as $id => $item) {
    $a[$id] = $item['title'];
    }
    
    $statuses = array();
    foreach (array('approved', 'hold', 'lock', 'wait')as $name) {
      $statuses[$name] = $lang->$name;
    }
    
    $id = $this->idget();
    if ($users->itemexists($id)) {
      $item = $users->getitem($id);
      $args->add($item);
      $args->add($pages->getitem($id));
      
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
        $args->group = tadminhtml::array2combo($a, $item['gid']);
        $args->status = tadminhtml::array2combo($statuses, $item['status']);
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
      'status' => 'hold',
      'trust' => 0,
      'idurl' => 0,
      'idview' => 1,
      'name' => '',
      'email' => '',
      'website' => '',
      'url' => '',
      'ip' => '',
      'avatar' => 0,
      'content' => '',
      'rawcontent' => '',
      'keywords' => '',
      'description' => '',
      'head' => ''
      );
      
      $args->add($item);
      $args->group = tadminhtml::array2combo($a, $item['gid']);
      $args->status = tadminhtml::array2combo($statuses, $item['status']);
      $result .= $html->form($args);
    }
    
    //table
    $perpage = 20;
    $count = $users->count;
    $from = $this->getfrom($perpage, $count);
    if ($users->dbversion) {
      $items = $users->select('', " order by id desc limit $from, $perpage");
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
    
    $theme = ttheme::i();
    $result .= $theme->getpages($this->url, litepublisher::$urlmap->page, ceil($count/$perpage));
    return $result;
  }
  
  public function processform() {
    $users = tusers::i();
    $groups = tusergroups::i();
    
    
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
      $id = $users->add($group, $login,$password, $name, $email, $website);
      if (!$id) return $this->html->h2->invalidregdata;
    } else {
      if (!$users->edit($id, $_POST))return $this->notfound;
    }
  }
  
}//class


?>