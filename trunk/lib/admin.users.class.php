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
  
  public function  gethead() {
    return parent::gethead() . tuitabs::gethead();
  }
  
  public function getcontent() {
    $result = '';
    $users = tusers::i();
    $groups = tusergroups::i();
    
    $html = $this->html;
    $lang = tlocal::i('users');
    $args = targs::i();

    $id = $this->idget();
switch ($this->action) {
case 'edit':
    if (!$users->itemexists($id)) {
$result .= $this->notfound();
} else {
    $statuses = array();
    foreach (array('approved', 'hold', 'lock', 'wait')as $name) {
      $statuses[$name] = $lang->$name;
    }
    
      $item = $users->getitem($id);
      $args->add($item);
$args->formtitle = $item['login'];
        $args->status = tadminhtml::array2combo($statuses, $item['status']);

    $tabs = new tuitabs();
$tabs->add($lang->login, '[text=login] [password=password] [text=email]');
$tabs->add($lang->rights, '[combo=status]' . 
tadmingroups::getgroups($item['idgroups']);
$tabs->add('Cookie', '[text=cookie] [text=expired] [text=registered] [text=trust]');

        $result .= $html->adminform($tabs->get(), $args);
      }
break;

case 'delete':
    if (!$users->itemexists($id)) {
$result .= $this->notfound();
} else {
        if  ($this->confirmed) {
          $users->delete($id);
          $result .= $html->h4->successdeleted;
        } else {
          $args->id = $id;
          $args->adminurl = $this->adminurl;
          $args->action = 'delete';
          $args->confirm = $lang->confirmdelete;
          $result .=$html->confirmform($args);
        }
}
break;

default:
$args->formtitle = $lang->newuser;
      $args->group = tadminhtml::array2combo($a, $item['gid']);
$args->login = '';
$args->email = '';
$args->action = 'add';
      $result .= $html->adminform(
'[text=login]
 [text=email]
[hidden=action]' . 
tadmingroups::getgroups(array()), $args);
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
switch ($this->action) {    
case 'add':
$_POST['idgroups'] = tadminhtml::check2array('idgroup-');
      if ($id = $users->add($_POST)) {
turlmap::redir("$this->adminurl=$id&action=edit");
} else {
return $this->html->h2->invalidregdata;
}
break;

case 'edit':
    $id = $this->idget();
if (!$users->itemexists($id)) return;
      if (!$users->edit($id, $_POST))return $this->notfound;
break;

default:
      $users->lock();
      foreach ($_POST as $key => $value) {
        if (!is_numeric($value)) continue;
        $id = (int) $value;
        $users->delete($id);
      }
      $users->unlock();
      return $this->html->h2->successdeleted;
}
  }
  
}//class
