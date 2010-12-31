<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminfoaf extends tadminmenu {
  
  private $user;
  
  public static function instance($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function gethtml($name = '') {
    $dir = dirname(__file__) .DIRECTORY_SEPARATOR  . 'resource' . DIRECTORY_SEPARATOR;
    if (!isset(tlocal::$data['foaf'])) {
      if (file_exists($dir . litepublisher::$options->language . '.ini')) {
        tlocal::loadini($dir . litepublisher::$options->language . '.ini');
      } else {
        tlocal::loadini($dir . 'en.ini');
      }
    }
    
    $html = tadminhtml::instance();
    if (!isset($html->ini['foaf'])) {
      $html->loadini($dir . 'html.ini');
    }
    
    return parent::gethtml($name = '');
  }
  
  private function getcombo($id, $status) {
    $lang = tlocal::instance('foaf');
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
      $items = array_slice(array_keys($foaf->items), $from, $perpage);
    }
    $html = $this->html;
    $result = $html->tableheader();
    $args = targs::instance();
    $args->adminurl = $this->adminurl;
    foreach ($items as $id )  {
      $item = $foaf->getitem($id);
      $args->add($item);
      $args->id = $id;
      $args->status = tlocal::$data['foaf'][$item['status']];
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
        if (!$foaf->itemexists($id)) return $this->notfound;
        $item = $foaf->getitem($id);
        $args = targs::instance();
        $args->add($item);
        $args->status = $this->getcombo($id, $item['status']);
        $result .= $html->editform($args);
        break;
        
        case 'delete':
        $id = $this->idget();
        if (!$foaf->itemexists($id)) return $this->notfound;
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
      ttheme::$vars['profile '] = $profile;
      $args = targs::instance();
      $args->gender = $profile->gender != 'female';
      $result .= $html->profileform($args);
      break;
      
      case 'profiletemplate':
      $profile = tprofile::instance();
      $args = targs::instance();
      $args->template = $profile->template;
      $result .= $html->profiletemplate($args);
      break;
    }
    
    return $html->fixquote($result);
  }
  
  public function processform() {
    $foaf = tfoaf::instance();
    $html = $this->html;
    
    switch ($this->name) {
      case 'foaf':
      if (!isset($_POST['foaftable'])) {
        extract($_POST);
        if ($this->action == 'edit') {
          $id = $this->idget();
          if (!$foaf->itemexists($id)) return '';
          $status = $_POST["status-$id"];
          $foaf->edit($id, $nick, $url, $foafurl, $status);
          return $html->h2->successedit;
        } else {
          if (empty($url))  return '';
          if ($foaf->hasfriend($url)) return $html->h2->erroradd;
          $foaf->addurl($url);
          return $html->h2->successadd;
        }
      } else {
        $status = isset($_POST['approve']) ? 'approved' : (isset($_POST['hold']) ? 'hold' : 'delete');
        $foaf->lock();
        foreach ($_POST as $key => $id) {
          if (!is_numeric($id))  continue;
          $id = (int) $id;
          if ($status == 'delete') {
            $foaf->delete($id);
          } else {
            $foaf->changestatus($id, $status);
          }
        }
        $foaf->unlock();
        return $html->h2->successmoderate;
      }
      
      case 'profile':
      $profile = tprofile::instance();
      foreach ($_POST as $key => $value) {
        if (isset($profile->data[$key])) $profile->data[$key] = $value;
      }
      $profile->gender = isset($_POST['gender']) ? 'male' : 'female';
      $profile->save();
      return $html->h2->successprofile;
      
      case 'profiletemplate':
      $profile = tprofile::instance();
      $profile->template = $_POST['template'];
      $profile->save();
      return $html->h2->successprofile;
    }
    
    return '';
  }
  
}//class

?>