<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminusers extends tadminmenu {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function getcontent() {
    $result = '';
    $users = tusers::instance();
$groups = tusergroups::instance();
$a = array();
foreach ($groups->items as $id => $item) {
$a['$id] = $item['name'];
}

    $html = $this->html;
    $args = targs::instance();
    $id = $this->idget();
if ($users->itemexists($id)) {
$item = $users->getitem($id);
} else {
    $item = array(
    'login' => '',
    'password' => '',
    'cookie' =>  '',
    'expired' => sqldate(),
    'gid' => 'nobody',
'trust' => 0,
'status' => 'wait',
    'name' => '',
    'email' => '',
    'url' => '',
'ip' => '',
'avatar' => 0
    );
}
$args->add($item);
      $result .= $html->form($args);

      if (isset($_GET['action']) &&($_GET['action'] == 'delete'))  {
        if  ($this->confirmed) {
          $users->delete($id);
          return $h2->successdeleted;
        } else {
          return $html->confirmdelete($args);
        }
      }
      
      $result .= $isusers ? $h2->edittag : $h2->editcategory;
      if (isset($_GET['full'])) {
        $args->add($users->contents->getitem($id));
        $args->iconlink = $users->geticonlink($id);
        $result .= $html->fullform($args);
      } else {
        $result = $html->form($args);
      }
    }
    
    //table
    $perpage = 20;
    $count = $users->count;
    $from = $this->getfrom($perpage, $count);
    
    if (dbversion) {
      $items = $users->select('', " order by id asc limit $from, $perpage");
      if (!$items) $items = array();
    } else {
      $items = array_slice(array_keys($users->items), $from, $perpage);
    }
    
    $result .= $html->listhead();
    foreach ($items as $id) {
      $item = $users->getitem($id);
      $args->add($item);
      $result .= $html->itemlist($args);
    }
    $result .= $html->listfooter;
    $result = $html->fixquote($result);
    
    $theme = ttheme::instance();
    $result .= $theme->getpages($this->url, litepublisher::$urlmap->page, ceil($count/$perpage));
    return $result;
  }
  
  public function processform() {
    if (empty($_POST['title'])) return '';
    extract($_POST);
    $isusers = $this->name == 'users';
    $users = $isusers  ? litepublisher::$classes->users : litepublisher::$classes->categories;
    $id = $this->idget();
    if ($id == 0) {
      $id = $users->add($title);
    } elseif (isset($_GET['full'])) {
      $item = $users->getitem($id);
      $icon = isset($icon) ? $icon : $item['icon'];
      $users->edit($id, $title, $url, $icon);
      $users->contents->edit($id, $rawcontent, $description, $keywords);
      if (isset($theme)) $users->contents->setvalue($id, 'theme', $theme);
    } else {
      $item = $users->getitem($id);
      $users->edit($id, $title, $item['url'], $item['icon']);
    }
    
    return sprintf($this->html->h2->success, $title);
  }
  
}//class


?>