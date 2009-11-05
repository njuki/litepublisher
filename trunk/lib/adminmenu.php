<?php

class tadminmenumanager extends tadminmenuitem {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function getcontent() {
    global $options;
$result = '';
      $menu = tmenu::instance();
$html = $this->html;   
$args = new targs();
$args->adminurl = $options->url . $this->url . $options->q . 'id';
$args->editurl = $options->url . $this->url . 'edit/' . $options->q . 'id';

    switch ($this->name) {
      case 'menu':
      if (isset($_GET['action']) && in_array($_GET['action'], array('delete', 'setdraft', 'publish'))) {
$action = $_GET['action'];
$id = /$this->idget();
    if (!$menu->itemexists($id))  return $this->notfound;
    $menuitem = tmenuitem::instance($id);
    if  (!$this->confirmed) {
$args->action = $action;
$args->confirm = sprintf($this->lang->confirm, tlocal::$data['poststatus'][$action], $menu->getlink($id));
return $this->html->confirmform($args);
} 

$h2 = $html->h2;
      switch ($action) {
        case 'delete' :
        $menu->delete($id);
$result .= $h2->confirmeddelete;
break;
        
        case 'setdraft':
        $menuitem->status = 'draft';
        $menu->edit($menuitem);
$result .= $h2->confirmedsetdraft;
break;
        
        case 'publish':
        $menuitem->status = 'published';
        $menu->edit($menuitem);
$result .= $h2->confirmedpublish;
        break;
      }

//list
$result .= $html->listhead();
      foreach ($menu->items as $id => $item) {
$args->id = $id;
$args->link = $menu->getlink($id);
$args->order = $item['order'];
        $args->status = TLocal::$data['poststatus'][$item['status']];
        if ($item['parent'] == 0) {
          $args->parent = '---';
        } else {
          $args->parent = $menu->getlink($id);
        }
$result .=$html->itemlist($args);
      }
$result .= $html->listfooter;
return str_replace("'", '"', $result);

      case 'edit':
      $id = $this->idget();
if (!$menu->itemexists($id)) return $this->notfound;
      $menuitem = tmenuitem::instance($id);
$args->id = $id;
$args->title = $menuitem->title;
$args->url = $menuitem->url;
$args->order = $menuitem->order;
    $args->published = $menuitem->status != 'draft' ? 'selected' : '';
    $args->draft = $menuitem->status == 'draft' ? 'selected' : '';
      $args->content = $menuitem->content;
      $selected = $menuitem->parent  == 0 ? 'selected' : '';
$parentcombo = "<option value='0' $selected>---</option>\n";
      foreach ($menu->items as $id => $item) {
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
$menu = tmenu::instance();
if (!$menu->itemexists($id)) return $this->notfound;
      $menuitem = tmenuitem::instance($id);
      $menuitem->title = $title;
$menuitem->url = $url;
      $menuitem->order = (int) $order;
      $menuitem->parent = (int) $parent;
    $menuitem->status = $status == 'draft' ? 'draft' : 'published';
      $menuitem->content = $content;

      if ($id == 0) {
        $menu->add($menuitem);
      } else  {
        $menu->edit($menuitem);
      }
  }
  
}//class
?>