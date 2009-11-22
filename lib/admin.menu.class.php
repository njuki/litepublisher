<?php
/**
 * Lite Publisher 
 * Copyright (C) 2010 Vladimir Yushko http://litepublisher.com/
 * Dual licensed under the MIT (mit.txt) 
 * and GPL (gpl.txt) licenses.
**/

class tadminmenumanager extends tadminmenu {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function getcontent() {
    global $options;
$result = '';
      $menus = tmenus::instance();
$html = $this->html;   
$args = targs::instance();
$args->adminurl = $this->adminurl;
$args->editurl = $options->url . $this->url . 'edit/' . $options->q . 'id';

    switch ($this->name) {
      case 'menu':
      if (isset($_GET['action']) && in_array($_GET['action'], array('delete', 'setdraft', 'publish'))) {
$result .= $this->doaction($this->idget(), $_GET['action']);
}
$result .= $this->getmenulist();
break;

      case 'edit':
      $id = $this->idget();
if (!$menus->itemexists($id)) return $this->notfound;
      $menuitem = tmenu::instance($id);
$args->id = $id;
$args->title = $menuitem->title;
$args->url = $menuitem->url;
$args->order = $menuitem->order;
    $args->published = $menuitem->status != 'draft' ? 'selected' : '';
    $args->draft = $menuitem->status == 'draft' ? 'selected' : '';
      $args->content = $menuitem->content;
      $selected = $menuitem->parent  == 0 ? 'selected' : '';
$parentcombo = "<option value='0' $selected>---</option>\n";
      foreach ($menus->items as $id => $item) {
        if ($id != $menuitem->id) {
          $selected = $menuitem->parent  == $id ? 'selected' : '';
          $parentcombo .= "<option value='$id' $selected>{$item['title']}</option>\n";
        }
              }
$args->parentcombo = $parentcombo;
return $html->editform($args);
    }
      }
  
  public function processform() {
    global $options;
if ($this->name != 'edit') return '';
    extract($_POST);
      if (empty($title) || empty($content)) return '';
      $id = $this->idget();
$menus = tmenu::instance();
if (!$menus->itemexists($id)) return $this->notfound;
      $menuitem = tmenu::instance($id);
      $menuitem->title = $title;
$menuitem->url = $url;
      $menuitem->order = (int) $order;
      $menuitem->parent = (int) $parent;
    $menuitem->status = $status == 'draft' ? 'draft' : 'published';
      $menuitem->content = $content;

      if ($id == 0) {
        $menus->add($menuitem);
      } else  {
        $menus->edit($menuitem);
      }
  }
  

private function getmenulist() {
$menus = tmenus::instance();
$args = targs::instance();
$args->adminurl = $this->adminurl;
$html = $this->html;
$result = $html->listhead();
      foreach ($menus->items as $id => $item) {
$args->id = $id;
$args->link = $menus->getlink($id);
$args->order = $item['order'];
        $args->status = TLocal::$data['poststatus'][$item['status']];
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
$args->action = 'delete';
$args->confirm = sprintf($this->lang->confirm, tlocal::$data['poststatus'][$action], $menus->getlink($id));
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