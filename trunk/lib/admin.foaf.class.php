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
  
  private function GetComboStatus($id, $status) {
    $langar = &TLocal::$data[$this->basename];
    $names = array('accepted', 'delete', 'hold', 'invated', 'rejected', 'ban');
    $result = "<select name='status-$id' >\n";
    
    foreach ($names as $name) {
      $selected = $status == $name ? 'selected' : '';
    $result .= "<option value='$name' $selected>{$langar[$name]}</option>\n";
    }
    $result .= "</select>";
    return $result;
  }
  
  public function Getcontent() {
    $foaf = &TFoaf::instance();
    $html = &THtmlResource::instance();
    $html->section = $this->basename;
    $lang = &TLocal::instance();
    
    $result = '';
    
    switch ($this->arg) {
      case null:
      eval('$result .= "'. $html->addform . '\n";');
      eval('$result .= "'. $html->tableheader . '\n";');
      foreach ($foaf->items as $id => $item) {
        eval('$result .= "'. $html->itemlist . '\n";');
      }
      eval('$result .= "'. $html->tablefooter. '\n";');;
      break;
      
      case 'edit':
      $id = !empty($_GET['id']) ? (int) $_GET['id'] : (!empty($_POST['id']) ? (int)$_POST['id'] : 0);
      if (!isset($foaf->items[$id])) {
        eval('$result = "'. $html->notfound  . '\n";');
        return $result;
      }
      $friend = $foaf->items[$id];
      $status = '';
      eval('$result .= "' . $html->editform . '\n";');
      break;
      
      case 'delete':
      $id = !empty($_GET['id']) ? (int) $_GET['id'] : (!empty($_POST['id']) ? (int)$_POST['id'] : 0);
      if (!isset($foaf->items[$id])) {
        eval('$result = "'. $html->notfound  . '\n";');
        return $result;
      }
      $friend = $foaf->items[$id];
      if (!empty($_GET['confirm']) && ($_GET['confirm'] == 1)) {
        $foaf->Delete($id);
        eval('$result = "'. $html->deleted . '\n";');
      } else {
        eval('$result .= "'. $html->confirmdelete . '\n";');
      }
      break;
      
      case 'moderate':
      eval('$result .= "'. $html->moderheader . '\n";');
      $manager = &TFoafManager::instance();
      foreach ($manager->items as $url => $item) {
        $status = $this->GetComboStatus($item['id'], $item['status']);
        eval('$result .= "'. $html->moderitem . '\n";');
      }
      eval('$result .= "'. $html->moderfooter . '\n";');;
      return $this->FixCheckall($result);
      
      case 'profile':
      $profile = &TProfile::instance();
      $gender = $profile->gender != 'female' ? "checked='checked'" : '';
      eval('$result .= "'. $html->profileform . '\n";');
      break;
    }
    
    return str_replace("'", '"', $result);
  }
  
  public function ProcessForm() {
    $foaf = &TFoaf::instance();
    $html = &THtmlResource::instance();
    $html->section = $this->basename;
    $lang = &TLocal::instance();
    
    switch ($this->arg) {
      case null:
      extract($_POST);
      if (empty($url))  return '';
      $manager = &TFoafManager::instance();
      if ($manager->Add($url)) {
        return $this->success('successadd');
      } else {
        return $this->success('erroradd');
      }
      
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