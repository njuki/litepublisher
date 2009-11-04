<?php

class tadminmenumanager extends tadminmenuitem {
  
  public static function instance() {
    return getinstance(__class__);
  }
  
  public function getcontent() {
    global $options;
      $menu = tmenu::instance();
$html = $this->html;   
$args = new targs();
$args->adminurl = $options->url . $this->url . $options->q . 'id';
$args->editurl = $options->url . '/admin/menu/edit/' . $options->q . 'id';

    switch ($this->arg) {
      case null:
      if (isset($_GET['action'])) return $this->doaction($_GET['action']);
$result = $html->listhead();
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
      $menuitem = tmenuitem::instance($id);
$args->id = $id;
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
    extract($_POST);
    switch ($this->arg) {
      case null:
      break;
      
      case 'edit':
      if (empty($title) || empty($content)) return '';
      $id = $this->idget();
      $menuitem = tmenuitem::instance($id);
      $menuitem->title = $title;
      $menuitem->order = (int) $order;
      $menuitem->parent = (int) $parent;
      $menuitem->content = $content;
      
      $menu = tmenu::instance();
      if ($id == 0) {
        $menu->add($menuitem);
      } else  {
        $menu->edit($menuitem);
      }
    }
    return '';
  }
  
  public function doaction($action) {
    global $options;
    $id = $this->idget();
    $html = $this->html;
        $menu = tmenu::instance();
    if (!$menu->itemexists($id))  return $this->notfound;
    $menuitem = tmenuitem::instance($id);
    $result ='';
    $actionname = TLocal::$data['poststatus'][$_GET['action']];
    if  (isset($_GET['confirm']) && ($_GET['confirm'] == 1)) {
      switch ($_GET['action']) {
        case 'delete' :
        $menu->Delete($id);
        break;
        
        case 'setdraft':
        $menuitem->status = 'draft';
        $menu->Edit($menuitem);
        break;
        
        case 'publish':
        $menuitem->status = 'published';
        $menu->Edit($menuitem);
        break;
      }
      eval('$s =  "'. $html->confirmed . '\n";');
      $result .=  sprintf($s, $actionname, "<a href='$options->url$menuitem->url'>$menuitem->title</a>");
    } else {
      $lang->section = $this->basename;
      $confirm = sprintf($lang->confirm, $actionname, "<a href='$options->url$menuitem->url'>$menuitem->title</a>");
      eval('$result .= "'. $html->confirmform . '\n";');
    }
    return $result;
  }
  
}//class
?>