<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminfoaf extends tadminmenu {
  
  private $user;
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  private function getcombo($id, $status) {
    $lang = tlocal::$instance('foaf');
    $names = array('approved', 'hold', 'invated', 'rejected', 'spam', 'error');
    $result = "<select name='status-$id' >\n";
    
    foreach ($names as $name) {
$title = $lang->$name;
      $selected = $status == $name ? 'selected' : '';
    $result .= "<option value='$name' $selected>$title</option>\n";
    }
    $result .= "</select>";
    return $result;
  }
  
  private function getlist() {
$foaf = tfoaf::instance();
$perpage = 20;
    $total = $foaf->getcount();
    $from = $this->getfrom($perpage, $total);
    if ($foaf->dbversion) {
      $items = $foaf->select('', " order by status asc, added desc limit $from, $perpage");
      if (!$items) $items = array();
    } else {
      $items = array_slice($foaf->items, $from, $perpage);
    }
$html = $this->html;
$result = $html->checkallscript;
$result .= $html->tableheader();
$args = targs::instance();
$args->adminurl = $this->adminurl;
      foreach ($items as $id )  {
      $item = $foaf->getitem($id);
      $args->add($item);
      $args->status = tlocal::$data['foaf'][$item['status']}];
$result .= $html->itemlist($args);
      }
      $result .= $html->tablefooter();
      
    $theme = ttheme::instance();
    $result .= $theme->getpages('/admin/foaf/', litepublisher::$urlmap->page, ceil($total/$perpage));
return $result;
}
  
  
  public function getcontent() {
      $result = '';
    $foaf = tfoaf::instance();
    $html = $this->html;

    switch ($this->name) {
      case 'foaf':
switch ($this->action) {
case false:
  $result = $html->addform();
      break;
      
      case 'edit':
$id = $this->idget();
      if (!$foaf->itemexists($id))) return $this->notfount;
      $item = $foaf->getitem($id);
      $args = targs::instance();
$args->add($item);
$args->combo = $this->getcombo($id, $item['status']);
      $result .= $html->editform($args);
      break;
      
      case 'delete':
$id = $this->idget();
      if (!$foaf->itemexists($id))) return $this->notfount;
if ($this->confirmed) {
        $foaf->delete($id);
$result .= $html->h2->deleted;
      } else {
      $item = $foaf->getitem($id);
      $args = targs::instance();
      $args->add($item);
      $args->adminurl = $this->adminurl;
      $args->action = 'delete';
      $args->confirm = $html->confirmdelete($args);
        $result .= $html->confirmform($args);
      }
      break;
}
  $result .= $this->getlist();
break;      

      case 'profile':
      $profile = tprofile::instance();
      $gender = $profile->gender != 'female' ? "checked='checked'" : '';
      eval('$result .= "'. $html->profileform . '\n";');
      break;
    }
    
    return $html->fixquote($result);
  }
  
  public function processform() {
    $foaf = tfoaf::instance();
    $html = $this->html;

    switch ($this->name) {
      case 'foaf':
      if (!isset($_POST[['foaftable'])) {

      extract($_POST);
      if (empty($url))  return '';
      if ($foaf->hasfriend($url)) return $html->h2->erroradd;
$foaf->add($url;
        return $html->h2->successadd;
      
      case 'edit':
      extract($_POST);
      $id = !empty($_GET['id']) ? (int) $_GET['id'] : (!empty($_POST['id']) ? (int)$_POST['id'] : 0);
      if (!isset($foaf->items[$id])) return '';
      $friend = &$foaf->items[$id];
      $friend['nick'] = $nick;
      $friend['blog'] = $url;
      $friend['foaf'] = $foafurl;
      $foaf->Save();
      return $this->success('successedit');
      
      case 'moderate':
      $manager = &TFoafManager::instance();
      $manager->Lock();
      $st = 'status-';
      $u = 'url-';
      $id = false;
      foreach ($_POST as $key => $value) {
        if(strncmp($key, $u, strlen($u)) == 0) {
          $id = (int) substr($key, strlen($u));
        } elseif ((strncmp($key, $st, strlen($st)) == 0) && ($id == substr($key, strlen($st))) &&
        ($url = $manager->GetUrlByID($id))) {
          $manager->SetStatus($url, $value);
        }
      }
      $manager->Unlock();
      return $this->success('successmoderate');
      
      case 'profile':
      $profile = &TProfile::instance();
      foreach ($_POST as $key => $value) {
        if (isset($profile->Data[$key])) $profile->Data[$key] = $value;
      }
      $profile->gender = isset($_POST['gender']) ? 'male' : 'female';
      $profile->Save();
      return $this->success('successprofile');
    }
    
    return '';
  }
  
  private function success($key) {
    $html = THtmlResource::instance();
    $html->section = $this->basename;
    $lang = &TLocal::instance();
    
    litepublisher::$urlmap->ClearCache();
  eval('$result = "'. $html->{$key} . '\n";');
    return $result;
  }
  
}//class

?>