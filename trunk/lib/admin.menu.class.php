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
  

private function getmenulist() {
$menu = tmenu::instance();
$args = targs::instance();
$args->adminurl = $this->adminurl;
$html = $this->html;
$result = $html->listhead();
      foreach ($menu->items as $id => $item) {
$args->id = $id;
$args->link = $menu->getlink($id);
$args->order = $item['order'];
        $args->status = TLocal::$data['poststatus'][$item['status']];
                  $args->parent = $item['parent'] == 0 ? '---' : $menu->getlink($id);
$result .=$html->itemlist($args);
      }
$result .= $html->listfooter;
return str_replace("'", '"', $result);
}

private function doaction($id, $action) {
$menu = tmenu::instance();
    if (!$menu->itemexists($id))  return $this->notfound;
$args = targs::instance();
$html = $this->html;
$h2 = $html->h2;
    $menuitem = tmenuitem::instance($id);
      switch ($action) {
        case 'delete' :
    if  (!$this->confirmed) {
$args->adminurl = $this->adminurl;
$args->action = 'delete';
$args->confirm = sprintf($this->lang->confirm, tlocal::$data['poststatus'][$action], $menu->getlink($id));
return $this->html->confirmform($args);
} else {
        $menu->delete($id);
return $h2->confirmeddelete;
}

        case 'setdraft':
        $menuitem->status = 'draft';
        $menu->edit($menuitem);
return $h2->confirmedsetdraft;

        case 'publish':
        $menuitem->status = 'published';
        $menu->edit($menuitem);
return $h2->confirmedpublish;
      }
return '';
}

}//class
?>