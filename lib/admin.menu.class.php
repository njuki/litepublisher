<?php
/**
* Lite Publisher
* Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminmenumanager extends tadminmenu {
  
  public static function instance($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function gettitle() {
    if (($this->name == 'edit') && ($this->idget() != 0)) {
      return $this->lang->edit;
    }
    return parent::gettitle();
  }
  
  public function getcontent() {
    $result = '';
    $menus = tmenus::instance();
    $html = $this->html;
    $args = targs::instance();
    $args->adminurl = $this->adminurl;
    $args->editurl = litepublisher::$site->url . $this->url . 'edit/' . litepublisher::$site->q . 'id';
    
    switch ($this->name) {
      case 'menu':
      if (isset($_GET['action']) && in_array($_GET['action'], array('delete', 'setdraft', 'publish'))) {
        $result .= $this->doaction($this->idget(), $_GET['action']);
      }
      $result .= $this->getmenulist();
      return $result;
      
      case 'edit':
      $id = $this->idget();
      if ($id == 0) {
        $args->id = 0;
        $args->title = '';
        $args->url = '';
        $args->order = 0;
        $args->published = 'selected';
        $args->draft = '';
        $args->content = '';
        $parent = 0;
      } else {
        if (!$menus->itemexists($id)) return $this->notfound;
        $menuitem = tmenu::instance($id);
        $args->id = $id;
        $args->title = $menuitem->title;
        $args->url = $menuitem->url;
        $args->order = $menuitem->order;
        $args->published = $menuitem->status != 'draft' ? 'selected' : '';
        $args->draft = $menuitem->status == 'draft' ? 'selected' : '';
        $args->content = $menuitem->content;
        $parent = $menuitem->parent;
      }
      
      $selected = $parent  == 0 ? 'selected' : '';
      $parentcombo = "<option value='0' $selected>---</option>\n";
      foreach ($menus->items as $idmenu => $item) {
        if ($id != $idmenu) {
          $selected = $parent  == $idmenu ? 'selected' : '';
        $parentcombo .= "<option value='$idmenu' $selected>{$item['title']}</option>\n";
        }
      }
      $args->parentcombo = $parentcombo;
      return $html->editform($args);
    }
  }
  
  public function processform() {
    if ($this->name != 'edit') return '';
    extract($_POST, EXTR_SKIP);
    if (empty($title)) return '';
    $id = $this->idget();
    $menus = tmenus::instance();
    if (($id != 0) && !$menus->itemexists($id)) return $this->notfound;
    $menuitem = tmenu::instance($id);
    $menuitem->title = $title;
    $menuitem->url = $url;
    $menuitem->order = (int) $order;
    $menuitem->parent = (int) $parent;
    $menuitem->status = $status == 'draft' ? 'draft' : 'published';
    $menuitem->content = $content;
    
    if ($id == 0) {
      $_POST['id'] = $menus->add($menuitem);
    } else  {
      $menus->edit($menuitem);
    }
    return sprintf($this->html->p->success,"<a href=\"$menuitem->link\" title=\"$menuitem->title\">$menuitem->title</a>");
  }
  
  private function getmenulist() {
    $menus = tmenus::instance();
    $args = targs::instance();
    $args->adminurl = $this->adminurl;
    $args->editurl = litepublisher::$site->url .$this->url . 'edit/' . litepublisher::$site->q . 'id';
    $html = $this->html;
    $result = $html->listhead();
    foreach ($menus->items as $id => $item) {
      $args->add($item);
      $args->link = $menus->getlink($id);
      $args->status = tlocal::$data['common'][$item['status']];
      $args->parent = $item['parent'] == 0 ? '---' : $menus->getlink($id);
      $result .=$html->itemlist($args);
    }
    $result .= $html->listfooter;
    return str_replace("'", '"', $result);
  }
  
  private function doaction($id, $action) {
    $menus = tmenus::instance();
    if (!$menus->itemexists($id))  return $this->notfound;
    $args = targs::instance();
    $html = $this->html;
    $h2 = $html->h2;
    $menuitem = tmenu::instance($id);
    switch ($action) {
      case 'delete' :
      if  (!$this->confirmed) {
        $args->adminurl = $this->adminurl;
        $args->id = $id;
        $args->action = 'delete';
        $args->confirm = sprintf($this->lang->confirm, tlocal::$data['common'][$action], $menus->getlink($id));
        return $this->html->confirmform($args);
      } else {
        $menus->delete($id);
        return $h2->confirmeddelete;
      }
      
      case 'setdraft':
      $menuitem->status = 'draft';
      $menus->edit($menuitem);
      return $h2->confirmedsetdraft;
      
      case 'publish':
      $menuitem->status = 'published';
      $menus->edit($menuitem);
      return $h2->confirmedpublish;
    }
    return '';
  }
  
}//class
?>